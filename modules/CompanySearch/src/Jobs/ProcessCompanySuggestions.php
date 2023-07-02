<?php

namespace Modules\CompanySearch\Jobs;

use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Modules\CompanySearch\Builders\CompanyBuilder;
use Modules\CompanySearch\Models\ApiResponse;
use Modules\CompanySearch\Models\Company;
use Modules\CompanySearch\Types\ApiType;
use Modules\CompanySearch\Types\CompanyType;
use Modules\CompanySearch\Types\ImportStage;

class ProcessCompanySuggestions implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, Batchable;

    public function handle(): void
    {
        $this->handleKvkSearch();
    }

    private function handleKvkSearch()
    {
        $apiResponses = ApiResponse::query()
            ->unprocessed()
            ->forCompany()
            ->forApi(ApiType::KVK_SEARCH)
            ->get();

        foreach ($apiResponses as $response) {
            $responseData = json_decode($response->response, true);

            $results = $responseData['resultaten'] ?? [];

            if (empty($results)) {
                $response->markAsProcessed();
                continue;
            }

            foreach ($results as $result) {
                $type = CompanyType::from($result['type']);

                if ($type === CompanyType::RECHTSPERSOON) {
                    continue;
                }

                /** @var Company $company */
                $company = Company::where('number', $result['kvkNummer'])
                    ->where('type', $type->value)
                    ->first();

                if (
                    $company &&
                    !in_array($company->import_stage, [ImportStage::IMPORTED->value, ImportStage::SUGGESTED->value])
                ) {
                    continue;
                }

                $builder = new CompanyBuilder($company);
                $builder->setActive(true);
                $builder->setImportStage(ImportStage::SUGGESTED);
                $builder->setType($type);
                $builder->setName(substr($result['handelsnaam'], 0, 200));
                $builder->setNumber($result['kvkNummer']);
                $builder->setLocationNumber($result['vestigingsnummer'] ?? '');
                $builder->setAddressStreet($result['straatnaam'] ?? '');

                if (isset($result['plaats'])) {
                    $builder->setAddressResidence($result['plaats']);
                }

                $builder->save();
            }

            $response->markAsProcessed();
        }
    }
}
