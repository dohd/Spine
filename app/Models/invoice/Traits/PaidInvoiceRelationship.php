<?php

namespace App\Models\invoice\Traits;

use App\Models\customer\Customer;
use App\Models\items\PaidInvoiceItem;

trait PaidInvoiceRelationship
{
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function items()
    {
        return $this->hasMany(PaidInvoiceItem::class, 'paidinvoice_id');
    }
}
