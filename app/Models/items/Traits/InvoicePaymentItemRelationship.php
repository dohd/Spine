<?php

namespace App\Models\items\Traits;

use App\Models\invoice\Invoice;
use App\Models\invoice_payment\InvoicePayment;

trait InvoicePaymentItemRelationship
{
    public function paid_invoice()
    {
        return $this->belongsTo(InvoicePayment::class, 'paidinvoice_id');
    }

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }
}
