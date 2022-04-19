<?php

namespace App\Models\loan\Traits;

use App\Models\account\Account;

trait LoanRelationship
{
    public function lender()
    {
        return $this->belongsTo(Account::class, 'lender_id');
    }

    public function bank()
    {
        return $this->belongsTo(Account::class, 'bank_id');
    }
}
