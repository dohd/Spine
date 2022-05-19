<?php

namespace App\Models\supplier\Traits;

use App\Models\bill\Bill;
use App\Models\purchaseorder\Purchaseorder;

/**
 * Class SupplierRelationship
 */
trait SupplierRelationship
{
    public function purchase_orders()
    {
        return $this->hasMany(Purchaseorder::class);
    }

    public function bills()
    {
        return $this->hasMany(Bill::class);
    }
}