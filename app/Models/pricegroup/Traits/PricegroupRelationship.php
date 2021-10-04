<?php

namespace App\Models\pricegroup\Traits;

use App\Models\pricegroup\PriceGroupVariation;
//use App\Models\pricegroup\ProductVariation;
use DB;
/**
 * Class WarehouseRelationship
 */
trait PricegroupRelationship
{
    public function products()
    {
        return $this->hasMany(PriceGroupVariation::class)->select('selling_price as total_value, id as items');
        //[DB::raw('selling_price as total_value'),'id As items']);
    }

    
}
