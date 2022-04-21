<?php

namespace App\Models\items\Traits;

trait JournalItemRelationship
{
    public function account()
    {
        return $this->belongsTo(Account::class);
    }
}