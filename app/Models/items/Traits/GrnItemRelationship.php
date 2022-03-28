<?php

namespace App\Models\items\Traits;

use App\Models\items\PurchaseorderItem;

trait GrnItemRelationship
{
    public function purchaseorder_item()
    {
        return $this->belongsTo(PurchaseorderItem::class, 'poitem_id');
    }
}
