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
trait PayrollRelationship
{
     public function payroll_items()
     {
        return $this->hasMany(PayrollItem::class, 'payroll_id');
     }
}
