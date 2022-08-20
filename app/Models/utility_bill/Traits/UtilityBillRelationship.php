<?php

namespace App\Models\utility_bill\Traits;

use App\Models\items\UtilityBillItem;
use App\Models\supplier\Supplier;

trait UtilityBillRelationship
{
    public function items()
    {
        return $this->hasMany(UtilityBillItem::class, 'bill_id');
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }
}
