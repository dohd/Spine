<?php

namespace App\Models\product\Traits;

use App\Models\product\Product;
use App\Models\product\ProductMeta;
use App\Models\warehouse\Warehouse;
use App\Models\pricegroup\Pricegroup;
use App\Models\pricegroup\PriceGroupVariation;

/**
 * Class ProductRelationship
 */
trait ProductVariationRelationship
{
    public function warehouse()
    {
        return $this->hasOne(Warehouse::class, 'id', 'warehouse_id');
    }

    public function product_serial()
    {
        return $this->hasMany(ProductMeta::class, 'ref_id', 'id')->where('rel_type', '=', 2)->withoutGlobalScopes();
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function v_prices()
    {
        return $this->hasOne(PriceGroupVariation::class, 'product_variation_id', 'id');
    }

    public function variation_price()
    {
        return $this->hasOneThrough(Pricegroup::class, PriceGroupVariation::class, 'product_variation_id', 'pricegroup_id');
    }
}
