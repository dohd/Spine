<?php

namespace App\Models\projectstock\Traits;

use App\Models\items\ProjectstockItem;
use App\Models\quote\Quote;
use App\Models\transaction\Transaction;

trait ProjectStockRelationship
{
    public function items()
    {
        return $this->hasMany(ProjectstockItem::class, 'project_stock_id');
    }

    public function quote()
    {
        return $this->belongsTo(Quote::class);
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class, 'projectstock_issuance_id');
    }
}
