<?php

namespace App\Models\supplier\Traits;

use App\Models\bill\Bill;
use App\Models\goodsreceivenote\Goodsreceivenote;
use App\Models\purchaseorder\Purchaseorder;

/**
 * Class SupplierRelationship
 */
trait SupplierRelationship
{
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