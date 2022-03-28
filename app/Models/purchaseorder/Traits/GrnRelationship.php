<?php

namespace App\Models\purchaseorder\Traits;

use App\Models\items\GrnItem;
use App\Models\purchaseorder\Purchaseorder;

trait GrnRelationship
{
    public function purchaseorder()
    {
        return $this->belongsTo(Purchaseorder::class);
    }

    public function items()
    {
        return $this->hasMany(GrnItem::class);
    }
}
