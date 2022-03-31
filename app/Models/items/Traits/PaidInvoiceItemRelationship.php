<?php

namespace App\Models\items\Traits;

use App\Models\invoice\Invoice;

trait PaidInvoiceItemRelationship
{
    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }
}
