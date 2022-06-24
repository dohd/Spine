<?php

namespace App\Models\creditnote\Traits;

use App\Models\bill\Bill;
use App\Models\customer\Customer;
use App\Models\invoice\Invoice;
use App\Models\supplier\Supplier;
use App\Models\transaction\Transaction;

trait CreditNoteRelationship
{
    public function debitnote_transactions()
    {
        return $this->hasMany(Transaction::class, 'tr_ref')->whereIn('tr_type', ['dnote']);
    }

    public function creditnote_transactions()
    {
        return $this->hasMany(Transaction::class, 'tr_ref')->whereIn('tr_type', ['cnote']);
    }

    public function bill()
    {
        return $this->belongsTo(Bill::class);
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
}