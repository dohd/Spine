<?php

namespace App\Models\items\Traits;

use App\Models\goodsreceivenote\Goodsreceivenote;
use App\Models\items\PurchaseorderItem;
use App\Models\product\ProductVariation;

trait GoodsreceivenoteItemRelationship
{
    public function goodsreceivenote()
    {
        return $this->belongsTo(Goodsreceivenote::class, 'goods_receive_note_id');
    }

    public function purchaseorder_item(Type $var = null)
    {
        return $this->belongsTo(PurchaseorderItem::class, 'purchaseorder_item_id');
    }

    public function productvariation(Type $var = null)
    {
        return $this->belongsTo(ProductVariation::class, 'item_id');
    }
}
