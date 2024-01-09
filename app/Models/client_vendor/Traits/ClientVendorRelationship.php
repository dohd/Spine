<?php

namespace App\Models\client_vendor\Traits;

use App\Models\customer\Customer;
use App\Models\hrm\Hrm;

trait ClientVendorRelationship
{
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function user()
    {
        return $this->hasOne(Hrm::class, 'client_vendor_id');
    }
}
