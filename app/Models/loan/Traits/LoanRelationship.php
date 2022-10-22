<?php

namespace App\Models\loan\Traits;

use App\Models\account\Account;
use App\Models\lender\Lender;
use App\Models\transaction\Transaction;

trait LoanRelationship
{
    public function transactions()
    {
        return $this->hasMany(Transaction::class, 'tr_ref')->where('tr_type', 'loan');
    }

    public function lender()
    {
        return $this->belongsTo(Lender::class, 'lender_id');
    }

    public function bank()
    {
        return $this->belongsTo(Account::class, 'bank_id');
    }
}
