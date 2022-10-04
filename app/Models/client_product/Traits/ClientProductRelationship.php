<?php

namespace App\Models\client_product\Traits;

trait ClientProductRelationship
{
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
}
