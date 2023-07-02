<?php

namespace Modules\CompanySearch\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Modules\CompanySearch\Models\Company;

class CompanyDetailsRequested
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    private Company $company;

    /**
     * Create a new event instance.
     *
     * @param Company $company
     */
    public function __construct(Company $company)
    {
        $this->company = $company;
    }

    /**
     * @return Company
     */
    public function getCompany(): Company
    {
        return $this->company;
    }
}
