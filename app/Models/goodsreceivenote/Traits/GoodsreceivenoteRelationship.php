<?php

namespace App\Models\goodsreceivenote\Traits;

use App\Models\items\GoodsreceivenoteItem;
use App\Models\purchaseorder\Purchaseorder;
use App\Models\supplier\Supplier;
use App\Models\transaction\Transaction;
use App\Models\utility_bill\UtilityBill;

trait GoodsreceivenoteRelationship
{
     public function transactions()
     {
          return $this->hasMany(Transaction::class, 'tr_ref')->where('tr_type', 'grn');
     }

     public function bill()
     {
          return $this->hasOne(UtilityBill::class, 'ref_id')->where('document_type', 'goods_receive_note');
     }

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
