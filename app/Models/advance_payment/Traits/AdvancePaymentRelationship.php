<?php

namespace App\Models\advance_payment\Traits;

use App\Models\Access\User\User;

trait AdvancePaymentRelationship
{
    public function employee()
    {
        return $this->belongsTo(User::class);
    }
}
