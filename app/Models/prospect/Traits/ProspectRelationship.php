<?php

namespace App\Models\prospect\Traits;

use App\Models\prospect_calllist\ProspectCallList;
use App\Models\remark\Remark;

/**
 * Class ProspectRelationsip* 
 **/
trait ProspectRelationship
{
    public function remarks()
    {
        return $this->hasMany(Remark::class)->orderBy('updated_at', 'DESC');
    }
    public function prospectcalllist()
    {
        return $this->hasMany(ProspectCallList::class,'prospect_id');
    }
}
