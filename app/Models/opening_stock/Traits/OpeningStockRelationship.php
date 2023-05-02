<?php

namespace App\Models\opening_stock\Traits;

use App\Models\items\OpeningStockItem;
use App\Models\warehouse\Warehouse;

trait OpeningStockRelationship
{
    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }
    
    public function items()
    {
        return $this->hasMany(OpeningStockItem::class);
    }
}
