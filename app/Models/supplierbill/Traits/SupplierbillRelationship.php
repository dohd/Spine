<?php

namespace App\Models\supplierbill\Traits;

use App\Models\items\SupplierbillItem;
use App\Models\supplier\Supplier;

trait SupplierbillRelationship
{
    public function items()
    {
        return $this->hasMany(SupplierbillItem::class, 'supplier_bill_id');
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }
}
