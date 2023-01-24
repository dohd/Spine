<?php

namespace App\Models\supplier_product\Traits;

use App\Models\supplier\Supplier;
use App\Models\product\ProductVariation;

trait SupplierProductRelationship
{
    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function products()
    {
        return $this->hasOne(ProductVariation::class, 'code', 'product_code');
    }
}
