<?php

namespace App\Models\manualjournal\Traits;

use App\Models\items\JournalItem;

trait JournalRelationship
{
    public function items()
    {
        return $this->hasMany(JournalItem::class, 'journal_id');
    }
}
