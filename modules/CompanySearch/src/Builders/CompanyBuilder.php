<?php

namespace Modules\CompanySearch\Builders;

use Carbon\Carbon;
use Illuminate\Support\Str;
use Modules\CompanySearch\Models\Company;
use Modules\CompanySearch\Types\CompanyType;
use Modules\CompanySearch\Types\ImportStage;

class CompanyBuilder
{
    private Company $company;

    public function __construct(Company $company = null)
    {
        $this->company = $company ?: new Company();
    }

    public function save(): Company
    {
        $oldSlug = $this->company->slug;
        $this->company->save();

        $newSlug = $this->generateSlug();

        if ($oldSlug !== $newSlug) {
            $this->company->slug = $newSlug;
            $this->company->save();
        }

        return $this->company;
    }

    private function generateSlug(): string
    {
        $name = Str::slug(substr($this->company->name, 0, 200));

        $slug = $name . '-' . $this->company->id;

        if ($this->company->address_residence) {
            $slug = $name . '-' . Str::slug($this->company->address_residence);
        }

        if (Company::where('slug', $slug)->where('id', '!=', $this->company->id)->exists()) {
            $slug .= '-' . $this->company->id;
        }

        return $slug;
    }

    public function setType(CompanyType $type): static
    {
        $this->company->type = $type;

        return $this;
    }

    public function setNumber(string $number): static
    {
        $this->company->number = $number;

        return $this;
    }

    public function setLocationNumber(string $number): static
    {
        $this->company->location_number = $number;

        return $this;
    }

    public function setActive(bool $active): static
    {
        $this->company->is_active = $active;

        return $this;
    }

    public function setDescription(?string $description): static
    {
        $this->company->description = $description;

        return $this;
    }

    public function setImportStage(ImportStage $stage): static
    {
        $this->company->import_stage = $stage->value;

        return $this;
    }

    public function setName(string $name): static
    {
        $this->company->name = $name;

        return $this;
    }

    public function setAltName(string $name): static
    {
        $this->company->alt_name = $name;

        return $this;
    }

    public function setWebsite(?string $website): static
    {
        $this->company->website = $website;

        return $this;
    }

    public function setRsin(?string $rsin): static
    {
        $this->company->rsin = $rsin;

        return $this;
    }

    public function setEmail(?string $email): static
    {
        $this->company->email = $email;

        return $this;
    }

    public function setBtw(?string $btw): static
    {
        $this->company->btw = $btw;

        return $this;
    }

    public function setIndustry(?string $industry): static
    {
        $this->company->industry = $industry;

        return $this;
    }

    public function setEmployeesSizeRange(?string $range): static
    {
        $this->company->employees_size_range = $range;

        return $this;
    }

    public function setAmountEmployees(int $amount): static
    {
        $this->company->amount_employees = $amount;

        return $this;
    }

    public function setAddressResidence(?string $addressResidence): static
    {
        $this->company->address_residence = $addressResidence;

        return $this;
    }

    public function setAddressStreet(?string $addressStreet): static
    {
        $this->company->address_street = $addressStreet;

        return $this;
    }

    public function setAddressProvince(?string $addressProvince): static
    {
        $this->company->address_province = $addressProvince;

        return $this;
    }

    public function setAddressHouseNumber(string|int|null $addressHouseNumber): static
    {
        $this->company->address_house_number = $addressHouseNumber;

        return $this;
    }

    public function setAddressZipcode(?string $addressZipcode): static
    {
        $this->company->address_zipcode = $addressZipcode;

        return $this;
    }

    public function setAddressAddition(?string $addressAddition): static
    {
        $this->company->address_addition = $addressAddition;

        return $this;
    }

    public function setAddressRemark(?string $addressRemark): static
    {
        $this->company->address_remark = $addressRemark;

        return $this;
    }

    public function setAddressLat(?string $lat): static
    {
        $this->company->address_lat = $lat;

        return $this;
    }

    public function setAddressLng(?string $lng): static
    {
        $this->company->address_lng = $lng;

        return $this;
    }

    public function setFullAddress(?string $fullAddress): static
    {
        $this->company->full_address = $fullAddress;

        return $this;
    }

    public function setPhone(?string $phone): static
    {
        $this->company->phone = $phone;

        return $this;
    }

    public function setDateOfCreation(?Carbon $date): static
    {
        $this->company->date_of_creation = $date;

        return $this;
    }

    public function setLinkedin(?string $url): static
    {
        $this->company->linkedin = $url;

        return $this;
    }

    public function setTwitter(?string $url): static
    {
        $this->company->twitter = $url;

        return $this;
    }

    public function setYoutube(?string $url): static
    {
        $this->company->youtube = $url;

        return $this;
    }

    public function setFacebook(?string $url): static
    {
        $this->company->facebook = $url;

        return $this;
    }

    public function setInstagram(?string $url): static
    {
        $this->company->instagram = $url;

        return $this;
    }

    public function setGithub(?string $url): static
    {
        $this->company->github = $url;

        return $this;
    }

    public function setCrunchbase(?string $url): static
    {
        $this->company->crunchbase = $url;

        return $this;
    }

    public function setColorBrand(?string $hex): static
    {
        $this->company->color_brand = $hex;

        return $this;
    }

    public function setColorAccent(?string $hex): static
    {
        $this->company->color_accent = $hex;

        return $this;
    }

    public function setGooglePlaceId(?string $id): static
    {
        $this->company->google_place_id = $id;

        return $this;
    }
}
