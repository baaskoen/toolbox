<?php

namespace Modules\CompanySearch\Listeners;

use Modules\CompanySearch\Events\SearchQueryExecuted;
use Modules\CompanySearch\Jobs\StoreCompanyKvkSearchJob;
use Modules\CompanySearch\Models\SearchQuery;
use Modules\CompanySearch\Types\ApiType;

class StoreSearchQueryApiResponses
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
     * @param SearchQueryExecuted $event
     * @return void
     */
    public function handle(SearchQueryExecuted $event): void
    {
        $this->processForCompany($event->getQuery());
    }

    private function processForCompany(SearchQuery $query): void
    {
        $apiResponseCount = $query->apiResponses()
            ->select('api_name')
            ->forCompany()
            ->forApi(ApiType::KVK_SEARCH)
            ->count();

        if (!$apiResponseCount) {
            StoreCompanyKvkSearchJob::dispatch($query);
        }
    }
}
