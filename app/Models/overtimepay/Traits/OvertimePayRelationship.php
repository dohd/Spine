<?php

namespace App\Models\overtimepay\Traits;

use App\Models\hrm\Hrm;
use App\Models\hrm\HrmMeta;

/**
 * ClassJobTitleRelationship
 */
trait OvertimePayRelationship
{
     public function employee()
     {
        return $this->belongsTo(Hrm::class, 'employee_id');
     }
}
