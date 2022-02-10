<?php

namespace App\Models\items\Traits;

use App\Models\invoice\Invoice;
use App\Models\quote\Quote;

/**
 * Class CustomerRelationship
 */
trait InvoiceItemRelationship
{
    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    public function quote()
    {
        return $this->belongsTo(Quote::class);
    }

    public function product()
    {
        return $this->belongsTo('App\Models\product\ProductVariation', 'product_id', 'product_id')->withoutGlobalScopes();
    }

    public function variation()
    {
        return $this->belongsTo('App\Models\product\ProductVariation', 'product_id', 'id')->withoutGlobalScopes();
    }
}
