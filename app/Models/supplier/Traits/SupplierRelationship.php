<?php

namespace App\Models\supplier\Traits;

use App\Models\goodsreceivenote\Goodsreceivenote;
use App\Models\purchaseorder\Purchaseorder;
use App\Models\utility_bill\UtilityBill;

/**
 * Class SupplierRelationship
 */
trait SupplierRelationship
{
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