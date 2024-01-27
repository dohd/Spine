<?php

namespace App\Models\manualjournal\Traits;

use App\Models\items\JournalItem;
use App\Models\transaction\Transaction;

trait JournalRelationship
{
    public function items()
    {
        return $this->hasMany(JournalItem::class, 'journal_id');
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class, 'man_journal_id');
    }
}
