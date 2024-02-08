<?php

namespace App\Models\charge\Traits;

use App\Models\transaction\Transaction;

trait ChargeRelationship
{
    public function bank()
    {
        return $this->belongsTo('App\Models\account\Account', 'bank_id');
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class, 'charge_id');
    }
}