<?php

namespace App\Models\items\Traits;

use App\Models\account\Account;

trait JournalItemRelationship
{
    public function account()
    {
        return $this->belongsTo(Account::class);
    }
}
