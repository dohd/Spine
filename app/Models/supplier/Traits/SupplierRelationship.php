<?php

namespace App\Models\supplier\Traits;

use App\Models\creditnote\CreditNote;
use App\Models\goodsreceivenote\Goodsreceivenote;
use App\Models\items\PaidbillItem;
use App\Models\purchaseorder\Purchaseorder;
use App\Models\utility_bill\UtilityBill;

/**
 * Class SupplierRelationship
 */
trait SupplierRelationship
{
    public function debit_notes()
    {
        return $this->hasMany(CreditNote::class);
    }

    public function payments()
    {
        return $this->hasManyThrough(PaidbillItem::class, UtilityBill::class, 'supplier_id', 'bill_id');
    }

    public function bills()
    {
        return $this->hasMany(UtilityBill::class);
    }

    public function goods_receive_notes()
    {
        return $this->hasMany(Goodsreceivenote::class);
    }

    public function purchase_orders()
    {
        return $this->hasMany(Purchaseorder::class);
    }
}