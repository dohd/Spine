<?php

namespace App\Models\verifiedjcs\Traits;

use App\Models\equipment\Equipment;

trait VerifiedJcRelationship
{
    public function equipment()
    {
        return $this->belongsTo(Equipment::class);
    }
}
