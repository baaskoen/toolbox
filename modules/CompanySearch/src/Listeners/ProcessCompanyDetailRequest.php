<?php

namespace Modules\CompanySearch\Listeners;

use Modules\CompanySearch\Events\CompanyDetailsRequested;
use Modules\CompanySearch\Jobs\StoreCompanyKvkDetailJob;
use Modules\CompanySearch\Jobs\StoreCompanyKvkVestigingJob;
use Modules\CompanySearch\Types\ApiType;

class ProcessCompanyDetailRequest
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param CompanyDetailsRequested $event
     * @return void
     */
    public function handle(CompanyDetailsRequested $event)
    {
        $company = $event->getCompany();

        $count = $company->apiResponses()
            ->forApi(ApiType::KVK_DETAIL)
            ->forCompany()
            ->count();

        if ($count === 0) {
            StoreCompanyKvkDetailJob::dispatch($company);
        }

        $count = $company->apiResponses()
            ->forApi(ApiType::KVK_VESTIGING)
            ->forCompany()
            ->count();

        if ($count === 0) {
            StoreCompanyKvkVestigingJob::dispatch($company);
        }
    }
}
