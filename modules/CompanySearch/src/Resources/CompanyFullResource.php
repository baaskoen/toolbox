<?php

namespace Modules\CompanySearch\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Modules\CompanySearch\Models\CompanyTag;
use Modules\CompanySearch\Types\CompanyType;

class CompanyFullResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'type' => CompanyType::from($this->type)->value,
            'detail_link' => route('company.detail', ['slug' => $this->id]),
            'name' => $this->name,
            'alt_name' => $this->alt_name,
            'kvk' => $this->number,
            'slug' => $this->slug,
            'location_number' => $this->location_number,
            'address_residence' => $this->address_residence,
            'address_country' => $this->address_country,
            'address_street' => $this->address_street,
            'address_zipcode' => $this->address_zipcode,
            'address_addition' => $this->address_addition,
            'address_remark' => $this->address_remark,
            'address_province' => $this->address_province,
            'website' => $this->website,
            'phone' => $this->phone,
            'email' => $this->email,
            'address_lat' => $this->address_lat,
            'address_lng' => $this->address_lng,
            'full_address' => $this->full_address,
            'color_brand' => $this->color_brand,
            'color_accent' => $this->color_accent,
            'linkedin' => $this->linkedin,
            'twitter' => $this->twitter,
            'facebook' => $this->facebook,
            'instagram' => $this->instagram,
            'youtube' => $this->youtube,
            'crunchbase' => $this->crunchbase,
            'github' => $this->github,
            'date_of_creation' => $this->date_of_creation?->format('Y-m-d') ?? null,
            'google_place_id' => $this->google_place_id,
            'btw' => $this->btw,
            'rsin' => $this->rsin,
            'amount_employees' => $this->amount_employees,
            'industry' => $this->industry,
            'description' => $this->description,
            'tags' => $this->tags->map(function (CompanyTag $tag) {
                return $tag->tag;
            })->toArray(),
            'logo_dark' => $this->logo_dark?->getFullUrl(),
            'logo_light' => $this->logo_light?->getFullUrl(),
            'icon_dark' => $this->icon_dark?->getFullUrl(),
            'icon_light' => $this->icon_light?->getFullUrl(),
            'symbol_light' => $this->symbol_light?->getFullUrl(),
            'symbol_dark' => $this->symbol_dark?->getFullUrl(),
            'is_active' => $this->is_active
        ];
    }
}
