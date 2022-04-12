<?php

namespace App\Models\creditnote;

use App\Models\customer\Customer;
use App\Models\invoice\Invoice;

trait CreditNoteRelationship
{
    public function invoice()
    {
        $this->belongsTo(Invoice::class);
    }

    public function customer()
    {
        $this->belongsTo(Customer::class);
    }
}