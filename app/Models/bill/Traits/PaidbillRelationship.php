<?php

namespace App\Models\bill\Traits;

use App\Models\items\PaidbillItem;

trait PaidbillRelationship
{
    public function items()
    {
        return $this->hasMany(PaidbillItem::class);
    }
}
