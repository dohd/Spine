<?php

namespace App\Models\stock_transfer\Traits;

use App\Models\items\StockTransferItem;

trait StockTransferRelationship
{
    public function items()
    {
        return $this->hasMany(StockTransferItem::class);
    }
}
