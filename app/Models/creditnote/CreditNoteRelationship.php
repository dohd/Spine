<?php

namespace App\Models\creditnote;

use App\Models\bill\Bill;
use App\Models\customer\Customer;
use App\Models\invoice\Invoice;
use App\Models\supplier\Supplier;

trait CreditNoteRelationship
{
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