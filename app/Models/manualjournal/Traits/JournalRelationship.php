<?php

namespace App\Models\manualjournal\Traits;

use App\Models\account\Account;
use App\Models\invoice\Invoice;
use App\Models\items\JournalItem;
use App\Models\transaction\Transaction;
use App\Models\utility_bill\UtilityBill;

trait JournalRelationship
{
    public function bill()
    {
        return $this->hasOne(UtilityBill::class, 'man_journal_id');
    }

    public function invoice()
    {
        return $this->hasOne(Invoice::class, 'man_journal_id');
    }

    public function ledger_account()
    {
        return $this->belongsTo(Account::class, 'account_id');
    }

    public function items()
    {
        return $this->hasMany(JournalItem::class, 'journal_id');
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class, 'man_journal_id');
    }
}
