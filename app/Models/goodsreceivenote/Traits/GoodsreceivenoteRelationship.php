<?php

namespace App\Models\goodsreceivenote\Traits;

use App\Models\items\GoodsreceivenoteItem;
use App\Models\purchaseorder\Purchaseorder;
use App\Models\supplier\Supplier;

trait GoodsreceivenoteRelationship
{
     public function supplier()
     {
          return $this->belongsTo(Supplier::class);
     }

     public function purchaseorder()
     {
          return $this->belongsTo(Purchaseorder::class);
     }

     public function items()
     {
          return $this->hasMany(GoodsreceivenoteItem::class, 'goods_receive_note_id');
     }
}
