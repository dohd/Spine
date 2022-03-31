<?php

namespace App\Models\invoice\Traits;

use App\Models\items\PaidInvoiceItem;

trait PaidInvoiceRelationship
{
    public function items()
    {
        return $this->hasMany(PaidInvoiceItem::class);
    }
}
