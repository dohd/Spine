<?php

namespace App\Models\tenant\Traits;

use App\Models\tenant_package\TenantPackage;

trait TenantRelationship
{
    public function package()
    {
        return $this->hasOne(TenantPackage::class, 'company_id');
    }
}
