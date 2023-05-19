<?php

namespace App\Models\payroll\Traits;

use App\Models\hrm\Hrm;
use App\Models\hrm\HrmMeta;
use App\Models\jobtitle\JobTitle;
use App\Models\salary\Salary;
use App\Models\payroll\PayrollItem;

/**
 * Class PayrollRelationship
 */
trait PayrollItemRelationship
{
     public function payroll()
     {
        return $this->belongsTo(Payroll::class);
     }
     public function employee()
     {
         return $this->hasOne(Hrm::class, 'employee_id');
     }
}
