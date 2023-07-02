<?php

namespace Modules\CompanySearch;

use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use Modules\CompanySearch\Events\CompanyDetailsRequested;
use Modules\CompanySearch\Events\SearchQueryExecuted;
use Modules\CompanySearch\Listeners\ProcessApiDetailResponses;
use Modules\CompanySearch\Listeners\ProcessApiSearchResponses;
use Modules\CompanySearch\Listeners\ProcessCompanyDetailRequest;
use Modules\CompanySearch\Listeners\StoreSearchQueryApiResponses;
use Modules\CompanySearch\Resources\CompanyBasicResource;

class CompanySearchServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../config/company.php', 'company'
        );

        $this->loadRoutesFrom(__DIR__ . '/../routes/api.php');

        CompanyBasicResource::withoutWrapping();

        Event::listen(SearchQueryExecuted::class, StoreSearchQueryApiResponses::class);
        Event::listen(SearchQueryExecuted::class, ProcessApiSearchResponses::class);

        Event::listen(CompanyDetailsRequested::class, ProcessCompanyDetailRequest::class);
        Event::listen(CompanyDetailsRequested::class, ProcessApiDetailResponses::class);

    }
}
