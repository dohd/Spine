<?php

namespace App\Models\account\Traits;

use App\Models\account\AccountType;
use App\Models\transaction\Transaction;

/**
 * Class AccountRelationship
 */
trait AccountRelationship
{
    public function accountType()
    {
        return $this->belongsTo(AccountType::class);
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }
}