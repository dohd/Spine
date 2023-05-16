<?php

namespace App\Models\calllist\Traits;

use App\Models\prospect\Prospect;
use App\Models\prospect_calllist\ProspectCallList;

/**
 * Class ProspectRelationsip* 
 **/
trait CallListRelationship
{
    public function prospects()
    {
        return $this->hasManyThrough(Prospect::class, ProspectCallList::class, 'call_id',  'id', 'id','prospect_id')->withoutGlobalScopes();
    }
    
}
