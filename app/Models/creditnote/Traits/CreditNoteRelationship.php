<?php

namespace App\Models\creditnote\Traits;

use App\Models\bill\Bill;
use App\Models\customer\Customer;
use App\Models\invoice\Invoice;
use App\Models\items\TaxReportItem;
use App\Models\supplier\Supplier;
use App\Models\transaction\Transaction;

trait CreditNoteRelationship
{
    public function credit_note_tax_reports()
    {
        return $this->hasMany(TaxReportItem::class, 'credit_note_id');
    }

    public function debit_note_tax_reports()
    {
        return $this->hasMany(TaxReportItem::class, 'debit_note_id');
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

    public function debitnote_transactions()
    {
        return $this->hasMany(Transaction::class, 'dnote_id');
    }

    public function creditnote_transactions()
    {
        return $this->belongsTo(Transaction::class, 'cnote_id');
    }
}