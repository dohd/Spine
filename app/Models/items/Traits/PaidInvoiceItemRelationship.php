<?php

namespace App\Models\items\Traits;

use App\Models\invoice\Invoice;
use App\Models\invoice\PaidInvoice;

trait PaidInvoiceItemRelationship
{
    public function paid_invoice()
    {
        return $this->belongsTo(PaidInvoice::class, 'paidinvoice_id');
    }

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }
}
