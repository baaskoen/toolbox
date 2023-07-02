<?php

namespace Modules\CompanySearch\Listeners;

use Modules\CompanySearch\Builders\CompanyBuilder;
use Modules\CompanySearch\Events\CompanyDetailsRequested;
use Modules\CompanySearch\Jobs\AppendMetaToCompanyJob;
use Modules\CompanySearch\Jobs\ProcessCompanyDetails;
use Modules\CompanySearch\Jobs\ProcessCompanyVestiging;
use Modules\CompanySearch\Types\ImportStage;

class ProcessApiDetailResponses
{
    public function handle(CompanyDetailsRequested $event)
    {
        ProcessCompanyDetails::dispatch($event->getCompany());
        ProcessCompanyVestiging::dispatch($event->getCompany());
        AppendMetaToCompanyJob::dispatch($event->getCompany());

        $builder = new CompanyBuilder($event->getCompany());
        $builder->setImportStage(ImportStage::META_ADDED);
        $builder->save();
    }
}
