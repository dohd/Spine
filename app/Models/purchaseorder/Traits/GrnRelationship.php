<?php

namespace App\Models\purchaseorder\Traits;

use App\Models\items\GrnItem;

trait GrnRelationship
{
    public function products()
    {
        return $this->hasMany(GrnItem::class);
    }
}
