<?php

namespace App\Models\items\Traits;

use App\Models\invoice\Invoice;

/**
 * Class InvoiceItemRelationship
 */
trait WithholdingItemRelationship
{
    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }
}
