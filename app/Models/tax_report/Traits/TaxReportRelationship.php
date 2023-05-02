<?php

namespace App\Models\tax_report\Traits;

use App\Models\items\TaxReportItem;

trait TaxReportRelationship
{
    public function items()
    {
        return $this->hasMany(TaxReportItem::class);
    }
}
