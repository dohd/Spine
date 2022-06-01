<?php

namespace App\Models\contract\Traits;

use App\Models\customer\Customer;

trait ContractRelationship
{
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
}
