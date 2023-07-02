<?php

namespace Modules\CompanySearch\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Modules\CompanySearch\Models\SearchQuery;
use Modules\CompanySearch\Types\SearchQueryType;

class SearchQueryExecuted
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    private SearchQuery $query;
    private SearchQueryType $queryType;

    /**
     * Create a new event instance.
     *
     * @param SearchQuery $query
     * @param SearchQueryType $queryType
     */
    public function __construct(SearchQuery $query, SearchQueryType $queryType)
    {
        $this->query = $query;
        $this->queryType = $queryType;
    }

    /**
     * @return SearchQuery
     */
    public function getQuery(): SearchQuery
    {
        return $this->query;
    }

    /**
     * @return SearchQueryType
     */
    public function getQueryType(): SearchQueryType
    {
        return $this->queryType;
    }
}
