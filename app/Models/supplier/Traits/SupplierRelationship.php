<?php

namespace App\Models\supplier\Traits;

use App\Models\bill\Bill;
use App\Models\goodsreceivenote\Goodsreceivenote;
use App\Models\purchaseorder\Purchaseorder;
use App\Models\utility_bill\UtilityBill;

/**
 * Class SupplierRelationship
 */
trait SupplierRelationship
{
    public function due_bills()
    {
        return $this->hasMany(UtilityBill::class);
    }

    public function goodsreceivenotes()
    {
        return $this->hasMany(Goodsreceivenote::class);
    }

    public function purchase_orders()
    {
        return $this->hasMany(Purchaseorder::class);
    }

    public function bills()
    {
        return $this->hasMany(Bill::class);
    }
}