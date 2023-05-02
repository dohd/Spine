<?php

namespace App\Models\stock_transfer\Traits;

use App\Models\items\StockTransferItem;
use App\Models\warehouse\Warehouse;

trait StockTransferRelationship
{
    public function items()
    {
        return $this->hasMany(StockTransferItem::class);
    }

    public function source_location()
    {
        return $this->belongsTo(Warehouse::class, 'source_id');
    }

    public function destination_location()
    {
        return $this->belongsTo(Warehouse::class, 'destination_id');
    }
}
