<?php

namespace Modules\CompanySearch\Listeners;

use Modules\CompanySearch\Events\SearchQueryExecuted;
use Modules\CompanySearch\Jobs\ProcessCompanySuggestions;

class ProcessApiSearchResponses
{
    /**
     * Handle the event.
     *
     * @return void
     */
    public function handle(SearchQueryExecuted $event)
    {
        ProcessCompanySuggestions::dispatch();
//        ProcessCompanyGoogleSearches::dispatch();
    }
}
