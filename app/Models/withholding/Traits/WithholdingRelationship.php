<?php

namespace App\Models\withholding\Traits;

use App\Models\items\WithholdingItem;

/**
 * Class WithholdingRelationship
 */
trait WithholdingRelationship
{
    public function items()
    {
        return $this->hasMany(WithholdingItem::class, 'withholding_id');
    }

    public function account()
    {
        return $this->belongsTo('App\Models\account\Account');
    }

    public function customer()
    {
        return $this->belongsTo('App\Models\customer\Customer');
    }
}
