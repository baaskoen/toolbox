<?php

namespace Modules\CompanySearch\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CompanyTag extends Model
{
    use HasFactory;

    protected $fillable = [
        'tag'
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }
}
