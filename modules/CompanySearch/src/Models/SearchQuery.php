<?php

namespace Modules\CompanySearch\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SearchQuery extends Model
{
    use HasFactory;

    protected $fillable = [
        'query',
        'spelling_parent_id'
    ];

    public function apiResponses(): HasMany
    {
        return $this->hasMany(ApiResponse::class);
    }
}
