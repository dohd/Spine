<?php

namespace App\Models\department\Traits;

use App\Models\hrm\Hrm;
use App\Models\hrm\HrmMeta;
use App\Models\jobtitle\JobTitle;

/**
 * Class DepartmentRelationship
 */
trait DepartmentRelationship
{
      public function users()
    {
        return $this->hasManyThrough(Hrm::class, HrmMeta::class, 'department_id', 'id', 'id', 'user_id');
    }

    public function jobtitles()
    {
        return $this->hasMany(JobTitle::class);
    }
}
