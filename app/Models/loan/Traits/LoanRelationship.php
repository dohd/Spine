<?php

namespace App\Models\loan\Traits;

use App\Models\Access\User\User;
use App\Models\account\Account;
use App\Models\lender\Lender;
use App\Models\transaction\Transaction;

trait LoanRelationship
{
    public function employee()
    {
        return $this->belongsTo(User::class, 'employee_id');
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class, 'loan_id');
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
