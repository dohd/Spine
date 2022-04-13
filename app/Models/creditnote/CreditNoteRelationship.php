<?php

namespace App\Models\creditnote;

use App\Models\customer\Customer;
use App\Models\invoice\Invoice;

trait CreditNoteRelationship
{
    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
}