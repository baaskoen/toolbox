<?php

namespace Modules\CompanySearch\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Modules\CompanySearch\Types\CompanyType;

class CompanyBasicResource extends JsonResource
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
        ];
    }
}
