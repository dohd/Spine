<?php

namespace App\Models\equipment\Traits;

//use App\Models\Toolkit\SurchargeItems;

/**
 * Class surchargeorderRelationship
 */
trait EquipmentToolkitRelationship
{
   

    // public function items()
    // {
    //     return $this->hasMany(ToolkitItems::class, 'item_id','id');
    // }

    public function item()
    {
        return $this->hasMany(Toolkit::class, 'toolkit_id','id');
    }
 }
