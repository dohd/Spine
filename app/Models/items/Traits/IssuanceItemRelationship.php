<?php

namespace App\Models\items\Traits;

use App\Models\product\ProductVariation;
use App\Models\warehouse\Warehouse;

trait IssuanceItemRelationship
{
    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function product()
    {
        return $this->belongsTo(ProductVariation::class, 'product_id');
    }
}