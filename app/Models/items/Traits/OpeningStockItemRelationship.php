<?php

namespace App\Models\items\Traits;

use App\Models\product\Product;
use App\Models\product\ProductVariation;

trait OpeningStockItemRelationship
{
    public function productvariation()
    {
        return $this->belongsTo(ProductVariation::class, 'product_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'parent_id');
    }
}                                                        
