<?php

namespace App\Models\items\Traits;

use App\Models\supplierbill\Supplierbill;

trait BillpaymentItemRelationship
{
    public function supplier_bill()
    {
        return $this->belongsTo(Supplierbill::class, 'bill_id');
    }
}
