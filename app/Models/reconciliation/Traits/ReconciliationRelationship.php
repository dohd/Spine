<?php

namespace App\Models\reconciliation\Traits;

use App\Models\account\Account;

trait ReconciliationRelationship
{
    public function account(Type $var = null)
    {
        return $this->belongsTo(Account::class);
    }
}