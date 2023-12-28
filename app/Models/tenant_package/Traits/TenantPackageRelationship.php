<?php

namespace App\Models\tenant_package\Traits;

use App\Models\tenant_package\TenantPackageItem;

trait TenantPackageRelationship
{
    public function items()
    {
        return $this->hasMany(TenantPackageItem::class, 'tenant_package_id');
    }
}
