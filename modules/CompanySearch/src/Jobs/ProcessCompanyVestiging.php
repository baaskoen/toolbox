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

class ProcessCompanyVestiging implements ShouldQueue
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
        if ($this->company->import_stage === ImportStage::COMPLETED->value) {
            return;
        }

        $response = $this->company->apiResponses()
            ->forCompany()
            ->unprocessed()
            ->forApi(ApiType::KVK_VESTIGING)
            ->first();

        if (!$response) {
            return;
        }

        $responseData = json_decode($response->response, true);


        $companyBuilder = (new CompanyBuilder($this->company));
        $companyBuilder->setImportStage(ImportStage::LOCATION_DETAILED);
        if (isset($responseData['rsin'])) {
            $companyBuilder->setRsin($responseData['rsin']);
        }

        if (isset($responseData['formeleRegistratiedatum'])) {
            $companyBuilder->setDateOfCreation(Carbon::parse($responseData['formeleRegistratiedatum']));
        } else {
            $companyBuilder->setDateOfCreation(Carbon::parse($responseData['materieleRegistratie']['datumAanvang']));
        }

        if (isset($responseData['totaalWerkzamePersonen'])) {
            $companyBuilder->setAmountEmployees($responseData['totaalWerkzamePersonen']);
        }

        $companyBuilder->setName($responseData['eersteHandelsnaam']);

        foreach ($responseData['handelsnamen'] ?? [] as $handelsNaam) {
            if ($handelsNaam['naam'] === $responseData['eersteHandelsnaam']) {
                continue;
            }

            if ($handelsNaam['volgorde'] === 1) {
                $companyBuilder->setAltName($handelsNaam['naam']);
                continue;
            }

            if ($handelsNaam['volgorde'] > 1) {
                $this->company->addTag($handelsNaam['naam']);
            }
        }

        if (isset($responseData['statutaireNaam'])) {
            $uniqueName = $responseData['statutaireNaam'];

            if ($uniqueName !== $this->company->alt_name && $uniqueName !== $this->company->name) {
                $this->company->addTag($uniqueName);
            }
        }

        foreach ($responseData['adressen'] as $address) {
            if ($address['type'] !== 'bezoekadres') {
                continue;
            }


            $companyBuilder->setFullAddress($address['volledigAdres']);
            $companyBuilder->setAddressStreet($address['straatnaam']);
            $companyBuilder->setAddressHouseNumber($address['huisnummer']);
            if (isset($address['huisnummerToevoeging'])) {
                $companyBuilder->setAddressAddition($address['huisnummerToevoeging']);
            }
            if (isset($address['toevoegingAdres'])) {
                $companyBuilder->setAddressRemark($address['toevoegingAdres']);
            }
            $companyBuilder->setAddressZipcode($address['postcode']);
            $companyBuilder->setAddressResidence($address['plaats']);
            $companyBuilder->setAddressLat($address['geoData']['gpsLatitude']);
            $companyBuilder->setAddressLng($address['geoData']['gpsLongitude']);
            break;
        }


        if (isset($responseData['websites']) && count($responseData['websites']) > 0) {
            $companyBuilder->setWebsite($responseData['websites'][0]);
        }

        foreach ($responseData['sbiActiviteiten'] as $sbi) {
            if ($sbi['indHoofdactiviteit'] === 'Ja') {
                $companyBuilder->setIndustry($sbi['sbiOmschrijving']);
                break;
            }
        }

        $companyBuilder->save();
        $response->markAsProcessed();
    }
}
