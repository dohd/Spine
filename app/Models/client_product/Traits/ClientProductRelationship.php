<?php

namespace App\Models\client_product\Traits;

use App\Models\customer\Customer;

trait ClientProductRelationship
{
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
}
