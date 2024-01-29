<?php

namespace App\Models\account\Traits;

use App\Models\account\AccountType;
use App\Models\manualjournal\Journal;
use App\Models\transaction\Transaction;

/**
 * Class AccountRelationship
 */
trait AccountRelationship
{
    public function gen_journal()
    {
        return $this->hasOne(Journal::class);
    }

    public function accountType()
    {
        return $this->belongsTo(AccountType::class);
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }
}