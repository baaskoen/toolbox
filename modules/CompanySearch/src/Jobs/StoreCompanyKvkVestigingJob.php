<?php

namespace Modules\CompanySearch\Jobs;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Modules\CompanySearch\Models\Company;
use Modules\CompanySearch\Types\ApiType;
use Modules\CompanySearch\Types\ResponseType;
use Modules\CompanySearch\Types\SearchQueryType;

class StoreCompanyKvkVestigingJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private Company $company;

    /**
     * Create a new job instance.
     *
     * @param Company $company
     */
    public function __construct(Company $company)
    {
        $this->company = $company;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $client = new Client([
            'base_uri' => 'https://api.kvk.nl',
            'headers' => [
                'apikey' => config('company.kvk.api_key')
            ],
            'verify' => false
        ]);

        $success = true;

        try {
            $response = $client->get("/api/v1/vestigingsprofielen/{$this->company->location_number}", [
                'query' => [
                    'geoData' => true
                ]
            ]);
        } catch (ClientException $exception) {
            if ($exception->getResponse()->getStatusCode() !== 404) {
                logger()->critical($exception->getMessage());
            }

            $response = $exception->getResponse();
            $success = false;
        }

        $this->company->apiResponses()
            ->updateOrCreate([
                'api_name' => ApiType::KVK_VESTIGING->value,
                'search_query_type' => SearchQueryType::COMPANY->value
            ], [
                'response' => $response->getBody()->getContents(),
                'data_type' => ResponseType::JSON->value,
                'headers' => $response->getHeaders(),
                'was_successful' => $success
            ]);
    }
}
