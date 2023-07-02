<?php

namespace Modules\CompanySearch\Jobs;

use Carbon\Carbon;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Modules\CompanySearch\Builders\CompanyBuilder;
use Modules\CompanySearch\Models\Company;
use Modules\CompanySearch\Types\ApiType;
use Modules\CompanySearch\Types\ImportStage;

class ProcessCompanyDetails implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, Batchable;

    private Company $company;

    /**
     * @param Company $company
     */
    public function __construct(Company $company)
    {
        $this->company = $company;
    }

    public function handle(): void
    {
        $response = $this->company->apiResponses()
            ->forCompany()
            ->unprocessed()
            ->forApi(ApiType::KVK_DETAIL)
            ->first();

        if (!$response) {
            return;
        }

        $companies = Company::where('number', $this->company->number)->get();
        $responseData = json_decode($response->response, true);

        foreach ($companies as $company) {
            if ($company->import_stage !== ImportStage::SUGGESTED->value) {
                continue;
            }

            $companyBuilder = (new CompanyBuilder($this->company));
            $companyBuilder->setImportStage(ImportStage::KVK_PROFILED);

            if (isset($responseData['formeleRegistratiedatum'])) {
                $companyBuilder->setDateOfCreation(Carbon::parse($responseData['formeleRegistratiedatum']));
            } else {
                $companyBuilder->setDateOfCreation(Carbon::parse($responseData['materieleRegistratie']['datumAanvang']));
            }

            if (isset($responseData['totaalWerkzamePersonen'])) {
                $companyBuilder->setAmountEmployees($responseData['totaalWerkzamePersonen']);
            }

            foreach ($responseData['sbiActiviteiten'] as $sbi) {
                if ($sbi['indHoofdactiviteit'] === 'Ja') {
                    $companyBuilder->setIndustry($sbi['sbiOmschrijving']);
                    break;
                }
            }

            $eigenaar = $responseData['_embedded']['eigenaar'];
            $vestiging = $responseData['_embedded']['hoofdvestiging'];
            if (isset($eigenaar['rsin'])) {
                $companyBuilder->setRsin($eigenaar['rsin']);
            }

            if ($vestiging['vestigingsnummer'] === $company->location_number) {
                if (isset($vestiging['websites']) && count($vestiging['websites']) > 0) {
                    $companyBuilder->setWebsite($vestiging['websites'][0]);
                }

                foreach ($vestiging['adressen'] as $address) {
                    if ($address['type'] !== 'bezoekadres') {
                        continue;
                    }

                    $companyBuilder->setFullAddress($address['volledigAdres']);
                    $companyBuilder->setAddressStreet($address['straatnaam']);
                    $companyBuilder->setAddressHouseNumber($address['huisnummer']);
                    $companyBuilder->setAddressZipcode($address['postcode']);
                    if (isset($address['huisnummerToevoeging'])) {
                        $companyBuilder->setAddressAddition($address['huisnummerToevoeging']);
                    }
                    if (isset($address['toevoegingAdres'])) {
                        $companyBuilder->setAddressRemark($address['toevoegingAdres']);
                    }
                    $companyBuilder->setAddressResidence($address['plaats']);
                    $companyBuilder->setAddressLat($address['geoData']['gpsLatitude']);
                    $companyBuilder->setAddressLng($address['geoData']['gpsLongitude']);
                    break;
                }
            }

            $companyBuilder->save();
        }

        $response->markAsProcessed();
    }
}
