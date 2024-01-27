<?php

namespace App\Models\reconciliation\Traits;

use App\Models\account\Account;
use App\Models\transaction\Transaction;

trait ReconciliationRelationship
{
    public function account()
    {
        return $this->belongsTo(Account::class);
    }

    public function items()
    {
        return $this->hasMany(Transaction::class);
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class, 'reconciliation_id');
    }
}