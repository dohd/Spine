<?php

namespace App\Models\tenant_service\Traits;

use App\Models\tenant_service\TenantServiceItem;

trait TenantServiceRelationship
{
    public function items() 
    {
        return $this->hasMany(TenantServiceItem::class);
    }
}
