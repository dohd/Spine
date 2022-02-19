<?php

namespace App\Models\branch\Traits;

use App\Models\customer\Customer;
use App\Models\lead\Lead;

/**
 * Class ProductcategoryRelationship
 */
trait BranchRelationship
{
    public function leads()
    {
        return $this->hasMany(Lead::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
}
