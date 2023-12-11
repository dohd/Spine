<?php

namespace App\Models\labour_allocation\Traits;

use App\Models\branch\Branch;
use App\Models\customer\Customer;
use App\Models\djc\Djc;
use App\Models\quote\Quote;
use App\Models\hrm\Hrm;
use App\Models\labour_allocation\LabourAllocation;

/**
 * Class ProductcategoryRelationship
 */
trait LabourAllocationItemRelationship
{
     public function employee()
     {
        return $this->belongsTo(Hrm::class, 'employee_id');
     }

     public function labour()
     {
        return $this->belongsTo(LabourAllocation::class, 'labour_id');
     }
}
