<?php

namespace App\Models\prospect_calllist\Traits;

use App\Models\prospect\Prospect;

/**
 * Class ProspectCallListRelationsip* 
 **/
trait ProspectCallListRelationship
{
    function prospect(){
    return $this->belongsTo(Prospect::class,'prospect_id');
    }
    function prospect_status(){
        return $this->belongsTo(Prospect::class,'prospect_id')->where('is_called',0);
    }
}
