<?php

namespace App\Models\items\Traits;

use App\Models\items\PurchaseorderItem;
use App\Models\purchaseorder\Grn;

trait GrnItemRelationship
{
    public function grn()
    {
        return $this->belongsTo(Grn::class);
    }

    public function purchaseorder_item()
    {
        return $this->belongsTo(PurchaseorderItem::class, 'poitem_id');
    }
}
