<?php

namespace App\Models\branch\Traits;

use App\Models\customer\Customer;

/**
 * Class ProductcategoryRelationship
 */
trait BranchRelationship
{
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
}
