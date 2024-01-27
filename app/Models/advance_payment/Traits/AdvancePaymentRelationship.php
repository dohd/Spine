<?php

namespace App\Models\advance_payment\Traits;

use App\Models\Access\User\User;
use App\Models\utility_bill\UtilityBill;

trait AdvancePaymentRelationship
{
    public function employee()
    {
        return $this->belongsTo(User::class);
    }

    public function bill()
    {
        return $this->hasOne(UtilityBill::class, 'ref_id')->where('document_type', 'advance_payment');
    }
}
