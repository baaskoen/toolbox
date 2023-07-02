<?php

namespace Modules\CompanySearch\Services;

use Illuminate\Support\Collection;
use Modules\CompanySearch\Events\SearchQueryExecuted;
use Modules\CompanySearch\Models\Company;
use Modules\CompanySearch\Models\SearchQuery;
use Modules\CompanySearch\Types\SearchQueryType;

class SearchService
{
    public function searchCompany(string $q, int $limit = 30): Collection
    {
        $q = strtolower(trim($q ?? ''));

        if (empty($q)) {
            return collect([]);
        }

        /** @var SearchQuery $query */
        $query = SearchQuery::firstOrCreate(['query' => $q]);

        SearchQueryExecuted::dispatch($query, SearchQueryType::COMPANY);

        $companies = Company::search($q)->get()->take($limit);

        if ($companies->count() === 0) {
            $companies = Company::where('name', 'LIKE', "%$q%")
                ->limit($limit)
                ->orWhere('alt_name', 'LIKE', "%$q%")
                ->get();
        }

        return $companies;
    }
}
