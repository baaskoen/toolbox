<?php

namespace Modules\CompanySearch\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Laravel\Scout\Searchable;
use Modules\CompanySearch\Types\CompanyType;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class Company extends Model implements HasMedia
{
    use HasFactory;
    use Searchable;
    use InteractsWithMedia;

    protected $guarded = [];

    protected $casts = [
        'meta' => 'array',
        'date_of_creation' => 'date',
        'date_of_date' => 'date'
    ];

    protected $dates = [
        'date_of_creation',
        'date_of_date'
    ];

    protected $with = [
        'media',
        'tags'
    ];

    public function toSearchableArray(): array
    {
        return [
            'name' => $this->name,
            'alt_name' => $this->alt_name,
            'address_residence' => $this->address_residence,
            'tags' => $this->tags->map(function (CompanyTag $tag) {
                return $tag->tag;
            })->toArray()
        ];
    }

    public function tags(): HasMany
    {
        return $this->hasMany(CompanyTag::class);
    }

    public function apiResponses(): HasMany
    {
        return $this->hasMany(ApiResponse::class);
    }

    public function scopeIsMainOffice(Builder $builder): Builder
    {
        return $builder->where('type', CompanyType::HOOFDVESTIGING->value);
    }

    public function addTag(string $tag): CompanyTag|Model
    {
        return $this->tags()->firstOrCreate(['tag' => $tag]);
    }

    public function deleteTag(string $tag): int
    {
        return $this->tags()->where('tag', $tag)->delete();
    }

    public function getLogoDarkAttribute()
    {
        return $this->media->where('name', 'logo_dark')->first();
    }

    public function getLogoLightAttribute()
    {
        return $this->media->where('name', 'logo_light')->first();
    }

    public function getIconDarkAttribute()
    {
        return $this->media->where('name', 'icon_dark')->first();
    }

    public function getIconLightAttribute()
    {
        return $this->media->where('name', 'icon_light')->first();
    }

    public function getSymbolDarkAttribute()
    {
        return $this->media->where('name', 'symbol_dark')->first();
    }

    public function getSymbolLightAttribute()
    {
        return $this->media->where('name', 'symbol_light')->first();
    }

    public function getImageUrlAttribute(): string
    {
        /** @var Media $image */
        $image = $this->symbolDark;

        if (!$image) {
            $image = $this->iconDark;
        }

        if (!$image) {
            $image = $this->logoDark;
        }

        if (!$image) {
            return config('app.url') . '/img/company-placeholder.svg';
        }

        return $image->getFullUrl();
    }
}
