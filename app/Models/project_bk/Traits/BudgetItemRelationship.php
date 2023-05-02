<?php

namespace App\Models\project\Traits;

use App\Models\product\ProductVariation;
use App\Models\project\Budget;

/**
 * Class ProjectRelationship
 */
trait BudgetItemRelationship
{
    public function product()
    {
        return $this->belongsTo(ProductVariation::class, 'product_id');
    }

    public function budget()
    {
        return $this->belongsTo(Budget::class);
    }
}
