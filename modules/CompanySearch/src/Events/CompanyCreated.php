<?php

namespace Modules\CompanySearch\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Modules\CompanySearch\Models\Company;

class CompanyCreated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    private Company $company;

    /**
     * Create a new event instance.
     *
     * @return void
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
