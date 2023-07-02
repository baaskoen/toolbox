<?php

namespace Modules\CompanySearch\Jobs;

use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Validator;
use Modules\CompanySearch\Builders\CompanyBuilder;
use Modules\CompanySearch\Helpers\Google;
use Modules\CompanySearch\Helpers\Link;
use Modules\CompanySearch\Models\ApiResponse;
use Modules\CompanySearch\Models\Company;
use Modules\CompanySearch\Types\ApiType;
use Modules\CompanySearch\Types\ResponseType;
use Modules\CompanySearch\Types\SearchQueryType;

class AppendMetaToCompanyJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private Company $company;

    /**
     * Create a new job instance.
     * @param Company $company
     */
    public function __construct(Company $company)
    {
        $this->company = $company;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $this->searchGooglePlace($this->company);
        $this->findGooglePlaceDetails($this->company);
        $this->addGooglePlace($this->company);

        if ($this->company->website) {
            if (!$this->company->btw) {
                $this->findContactData($this->company);
                $this->addContactData($this->company);
            }

            $this->findBranding($this->company);
            $this->addBranding($this->company);
        }

        if (!$this->company->description) {
            $this->addDescription($this->company);
        }
    }

    protected function searchGooglePlace(Company $company): void
    {
        $count = $company->apiResponses()
            ->forApi(ApiType::GOOGLE_PLACE_SEARCH)
            ->forCompany()
            ->count();

        if ($count > 0) {
            return;
        }

        $success = true;

        try {
            $response = Google::searchPlace("{$company->name} {$company->address_residence}");
        } catch (ClientException $exception) {
            if ($exception->getResponse()->getStatusCode() !== 404) {
                $success = false;
                logger()->critical($exception->getMessage());
            }

            $response = $exception->getResponse();
        }

        $company->apiResponses()
            ->updateOrCreate([
                'api_name' => ApiType::GOOGLE_PLACE_SEARCH->value,
                'search_query_type' => SearchQueryType::COMPANY->value
            ], [
                'response' => $response->getBody()->getContents(),
                'data_type' => ResponseType::JSON->value,
                'headers' => $response->getHeaders(),
                'was_successful' => $success
            ]);
    }

    protected function findGooglePlaceDetails(Company $company): void
    {
        $apiResponse = $company->apiResponses()
            ->forApi(ApiType::GOOGLE_PLACE_SEARCH)
            ->unprocessed()
            ->forCompany()
            ->first();

        if (!$apiResponse) {
            return;
        }

        $responseData = json_decode($apiResponse->response, true);

        $apiResponse->markAsProcessed();

        if ($responseData['status'] !== "OK" || count($responseData['candidates']) === 0) {
            return;
        }

        $success = true;

        try {
            $response = Google::getPlace($responseData['candidates'][0]['place_id']);
        } catch (ClientException $exception) {
            if ($exception->getResponse()->getStatusCode() !== 404) {
                $success = false;
                logger()->critical($exception->getMessage());
            }

            $response = $exception->getResponse();
        }

        $company->apiResponses()
            ->updateOrCreate([
                'api_name' => ApiType::GOOGLE_PLACE_DETAILS->value,
                'search_query_type' => SearchQueryType::COMPANY->value
            ], [
                'response' => $response->getBody()->getContents(),
                'data_type' => ResponseType::JSON->value,
                'headers' => $response->getHeaders(),
                'was_successful' => $success
            ]);
    }

    protected function findContactData(Company $company): void
    {
        $count = $company->apiResponses()
            ->forApi(ApiType::WEBSITE_CONTACT)
            ->forCompany()
            ->count();

        if ($count > 0) {
            return;
        }

        $success = true;

        $client = new Client(['verify' => false]);
        $response = null;

        try {
            $response = $client->get("{$company->website}/contact");
        } catch (Exception $exception) {
            try {
                $response = $client->get("{$company->website}/nl/contact");
            } catch (Exception $exception) {
                $success = false;
            }
        }

        $company->apiResponses()
            ->updateOrCreate([
                'api_name' => ApiType::WEBSITE_CONTACT->value,
                'search_query_type' => SearchQueryType::COMPANY->value
            ], [
                'response' => $response ? $response->getBody()->getContents() : '{}',
                'data_type' => ResponseType::HTML->value,
                'headers' => $response ? $response->getHeaders() : [],
                'was_successful' => $success
            ]);
    }

    protected function findBranding(Company $company): void
    {
        $count = $company->apiResponses()
            ->forApi(ApiType::BRANDING)
            ->forCompany()
            ->count();

        if ($count > 0) {
            return;
        }

        $success = true;

        $host = Link::getHost($company->website);

        try {
            $client = new Client([
                'base_uri' => 'https://api.brandfetch.io',
                'headers' => [
                    'Authorization' => 'Bearer ' . config('company.brandfetch.api_key')
                ],
            ]);

            $response = $client->get("v2/brands/{$host}");
        } catch (ClientException $exception) {
            if ($exception->getResponse()->getStatusCode() !== 404) {
                $success = false;
                logger()->critical($exception->getMessage());
            }

            $response = $exception->getResponse();
        }

        $company->apiResponses()
            ->updateOrCreate([
                'api_name' => ApiType::BRANDING->value,
                'search_query_type' => SearchQueryType::COMPANY->value
            ], [
                'response' => $response->getBody()->getContents(),
                'data_type' => ResponseType::JSON->value,
                'headers' => $response->getHeaders(),
                'was_successful' => $success
            ]);
    }

    protected function addGooglePlace(Company $company)
    {
        /** @var ApiResponse $response */
        $response = $company->apiResponses()
            ->forApi(ApiType::GOOGLE_PLACE_DETAILS)
            ->forCompany()
            ->unprocessed()
            ->first();

        if (!$response) {
            return;
        }

        $data = json_decode($response->response, true)['result'];

        $foundZipcode = null;

        foreach ($data['address_components'] as $component) {
            if (in_array('postal_code', $component['types'])) {
                $foundZipcode = $component['long_name'];
                break;
            }
        }

        if (
            str_replace(' ', '', $foundZipcode) !==
            str_replace(' ', '', $company->address_zipcode)
        ) {
            return;
        }

        $builder = new CompanyBuilder($company);
        $builder->setGooglePlaceId($data['place_id']);
        if (isset($data['international_phone_number'])) {
            $builder->setPhone($data['international_phone_number']);
        }

        if (isset($data['website'])) {
            $builder->setWebsite($data['website']);
        }

        foreach ($data['address_components'] as $component) {
            if (in_array('administrative_area_level_1', $component['types'])) {
                $builder->setAddressProvince($component['long_name']);
            }
        }

        $builder->save();

        $response->markAsProcessed();
    }

    protected function addContactData(Company $company)
    {
        $response = $company->apiResponses()
            ->forApi(ApiType::WEBSITE_CONTACT)
            ->forCompany()
            ->unprocessed()
            ->first();

        if (!$response) {
            return;
        }

        $data = $response->response;

        $emailPattern = '/[a-z0-9_\-\+\.]+@[a-z0-9\-]+\.([a-z]{2,4})(?:\.[a-z]{2})?/i';
        preg_match_all($emailPattern, $data, $emailMatches);

        $builder = new CompanyBuilder($company);

        if (count($emailMatches[0]) > 0) {
            foreach ($emailMatches[0] as $email) {

                $validator = Validator::make(
                    [
                        'email' => $email
                    ],
                    [
                        'email' => 'email:rfc,dns'
                    ]
                );

                if ($validator->passes()) {
                    $builder->setEmail($email);
                    break;
                }
            }
        }

        $btwPattern = '/(NL[\d|\.]+B\d\d)/';
        preg_match_all($btwPattern, $data, $btwMatches);

        if (count($btwMatches[0]) > 0) {
            $btw = str_replace('.', '', $btwMatches[0][0]);

            if (strlen($btw) === 14) {
                $builder->setBtw($btwMatches[0][0]);
            }
        }

        $builder->save();

        $response->markAsProcessed();
    }

    protected function addBranding(Company $company)
    {
        $response = $company->apiResponses()
            ->forApi(ApiType::BRANDING)
            ->forCompany()
            ->unprocessed()
            ->first();

        if (!$response) {
            return;
        }

        $responseData = json_decode($response->response, true);

        $builder = new CompanyBuilder($company);

        foreach ($responseData['links'] ?? [] as $link) {
            if ($link['name'] === 'twitter') {
                $builder->setTwitter($link['url']);
            }

            if ($link['name'] === 'facebook') {
                $builder->setFacebook($link['url']);
            }

            if ($link['name'] === 'instagram') {
                $builder->setInstagram($link['url']);
            }

            if ($link['name'] === 'github') {
                $builder->setGithub($link['url']);
            }

            if ($link['name'] === 'youtube') {
                $builder->setYoutube($link['url']);
            }

            if ($link['name'] === 'linkedin') {
                $builder->setLinkedin($link['url']);
            }

            if ($link['name'] === 'crunchbase') {
                $builder->setCrunchbase($link['url']);
            }
        }

        foreach ($responseData['logos'] ?? [] as $logo) {
            $found = null;

            if ($logo['type'] === 'other') {
                continue;
            }

            foreach ($logo['formats'] as $format) {
                if ($format['format'] === 'png' || $format['format'] === 'svg') {
                    $found = $format['src'];
                }
            }

            if ($found) {
                $name = $logo['type'] . '_' . $logo['theme'];

                foreach ($company->media()->where('name', $name)->get() as $media) {
                    $company->deleteMedia($media->id);
                }

                $company->addMediaFromUrl($found)
                    ->setName($logo['type'] . '_' . $logo['theme'])
                    ->toMediaCollection();
            }

        }

        $colorAccent = null;
        $colorBrand = null;

        foreach ($responseData['colors'] ?? [] as $color) {
            if ($color['type'] === 'accent' && !$colorAccent) {
                $colorAccent = $color['hex'];
            }

            if ($color['type'] === 'brand' && !$colorBrand) {
                $colorBrand = $color['hex'];
            }
        }

        $builder->setColorBrand($colorBrand);
        $builder->setColorAccent($colorAccent);

        $builder->save();

        $response->markAsProcessed();
    }

    protected function addDescription(Company $company)
    {
        $description = "{$company->name} is een bedrijf gevestigd in {$company->address_residence}. ";
        $description .= "Het bedrijf bevindt zich in de industrie: {$company->industry}.";

        $builder = new CompanyBuilder($company);
        $builder->setDescription($description);
        $builder->save();
    }
}
