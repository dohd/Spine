<?php

namespace App\Models\issuance\Traits;

use App\Models\items\IssuanceItem;
use App\Models\quote\Quote;

trait IssuanceRelationship
{
    public function quote()
    {
        return $this->belongsTo(Quote::class);
    }

    public function items()
    {
        return $this->hasMany(IssuanceItem::class);
    }
}