<?php

namespace App\Models\tenant_service\Traits;

use App\Models\Company\Company;

trait TenantServiceRelationship
{
    public function company() 
    {
        return $this->belongsTo(Company::class);
    }
}
