<?php

namespace App\Models\purchase_request\Traits;

use App\Models\Access\User\User;

trait PurchaseRequestRelationship
{
    public function employee()
    {
        return $this->belongsTo(User::class, 'employee_id');
    }
}
