<?php

namespace App\Models\supplier_product\Traits;

use App\Models\supplier\Supplier;

trait SupplierProductRelationship
{
    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }
}
