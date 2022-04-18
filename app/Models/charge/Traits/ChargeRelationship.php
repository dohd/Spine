<?php

namespace App\Models\charge\Traits;

trait ChargeRelationship
{
    public function bank()
    {
        return $this->belongsTo('App\Models\account\Account', 'bank_id');
    }
}