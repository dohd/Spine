<?php

namespace App\Models\calllist\Traits;

use App\Models\remark\Remark;

/**
 * Class ProspectRelationsip* 
 **/
trait CallListRelationship
{
    public function remarks()
    {
        return $this->hasMany(Remark::class)->orderBy('updated_at', 'DESC');
    }
}
