<?php

namespace App\Models\productcategory\Traits;

use App\Models\product\Product;
use App\Models\product\ProductVariation;

/**
 * Class ProductcategoryRelationship
 */
trait ProductcategoryRelationship
{
    public function subcategories()
    {
        return $this->hasMany(Self::class, 'rel_id', 'id');
    }

    public function products()
    {
        return $this->hasManyThrough(ProductVariation::class, Product::class, 'productcategory_id', 'parent_id')->withoutGlobalScopes();
    }
}
