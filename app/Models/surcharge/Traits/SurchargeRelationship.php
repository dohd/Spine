<?php

namespace App\Models\surcharge\Traits;

use App\Models\surcharge\SurchargeItems;

/**
 * Class surchargeorderRelationship
 */
trait SurchargeRelationship
{
   

    // public function items()
    // {
    //     return $this->hasMany(surchargeItems::class, 'item_id','id');
    // }

    public function item()
    {
        return $this->hasMany(SurchargeItems::class, 'surcharge_id','id');
    }
 }
