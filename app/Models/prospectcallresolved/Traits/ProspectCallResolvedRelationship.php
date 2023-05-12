<?php

namespace App\Models\prospectcallresolved\Traits;
use App\Models\prospect\Prospect;

/**
 * Class ProspectRelationsip* 
 **/
trait ProspectCallResolvedRelationship
{
    function prospect(){
        return $this->belongsTo(Prospect::class,'prospect_id');
        }
}
