<?php

namespace Modules\CompanySearch\Controllers;

use Illuminate\Http\Request;
use Modules\CompanySearch\Events\CompanyDetailsRequested;
use Modules\CompanySearch\Models\Company;
use Modules\CompanySearch\Resources\CompanyBasicResource;
use Modules\CompanySearch\Resources\CompanyFullResource;
use Modules\CompanySearch\Services\SearchService;

class CompanySearchController
{
    public function search(Request $request)
    {
        $companies = (new SearchService())->searchCompany($request->query('query'));

        return CompanyBasicResource::collection($companies);
    }

    public function details(string|int $slug)
    {
        $company = Company::where('slug', $slug)
            ->orWhere('number', $slug)
            ->orWhere('id', $slug)
            ->firstOrFail();

        $originalStage = $company->import_stage;

        CompanyDetailsRequested::dispatch($company);

        if ($originalStage !== $company->import_stage) {
            $company = Company::find($company->id);
        }

        return new CompanyFullResource($company);
    }
}
