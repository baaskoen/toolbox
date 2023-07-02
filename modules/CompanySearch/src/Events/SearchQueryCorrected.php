<?php

namespace Modules\CompanySearch\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Modules\CompanySearch\Models\SearchQuery;

class SearchQueryCorrected
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    private SearchQuery $query;

    /**
     * Create a new event instance.
     *
     * @param SearchQuery $query
     */
    public function __construct(SearchQuery $query)
    {
        $this->query = $query;
    }

    /**
     * @return SearchQuery
     */
    public function getQuery(): SearchQuery
    {
        return $this->query;
    }
}
