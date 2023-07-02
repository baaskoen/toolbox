<?php

namespace Modules\CompanySearch\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\CompanySearch\Types\ApiType;
use Modules\CompanySearch\Types\SearchQueryType;

class ApiResponse extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'headers' => 'array',
        'meta' => 'array'
    ];

    public function searchQuery(): BelongsTo
    {
        return $this->belongsTo(SearchQuery::class);
    }

    public function scopeForCompany(Builder $query): Builder
    {
        return $query->where('search_query_type', SearchQueryType::COMPANY->value);
    }

    public function scopeForApi(Builder $query, ApiType $api): Builder
    {
        return $query->where('api_name', $api->name);
    }

    public function scopeUnprocessed(Builder $query): Builder
    {
        return $query->where('processed', false);
    }

    public function markAsProcessed(): void
    {
        $this->processed = true;
        $this->save();
    }
}
