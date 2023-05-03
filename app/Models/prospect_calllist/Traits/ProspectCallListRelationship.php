<?php

namespace App\Models\prospect_calllist\Traits;

use App\Models\remark\Remark;

/**
 * Class ProspectCallListRelationsip* 
 **/
trait ProspectCallListRelationship
{
    public function remarks()
    {
        return $this->hasMany(Remark::class)->orderBy('updated_at', 'DESC');
    }
}
