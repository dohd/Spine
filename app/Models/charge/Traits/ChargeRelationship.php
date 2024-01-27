<?php

namespace App\Models\charge\Traits;

use App\Models\charge\Charge;

trait ChargeRelationship
{
    public function bank()
    {
        return $this->belongsTo('App\Models\account\Account', 'bank_id');
    }

    public function transactions()
    {
        return $this->hasMany(Charge::class, 'charge_id');
    }
}