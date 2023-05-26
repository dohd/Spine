<?php

namespace App\Models\hrm\Traits;

use App\Models\department\Department;
use App\Models\hrm\HrmMeta;
use App\Models\jobtitle\jobtitle;

/**
 * Class HrmRelationship
 */
trait HrmMetaRelationship
{

   
    public function jobtitle()
    {
        return $this->hasOne(jobtitle::class, 'id','position');
    }
    public function department()
    {
        return $this->hasOne(Department::class, 'id','department_id');
    }

}
