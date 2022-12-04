<?php

namespace App\Models\items\Traits;

use App\Models\utility_bill\UtilityBill;

trait BillpaymentItemRelationship
{
    public function supplier_bill()
    {
        return $this->belongsTo(UtilityBill::class, 'bill_id');
    }

    public function bill()
    {
        return $this->belongsTo(UtilityBill::class, 'bill_id');
    }
}
