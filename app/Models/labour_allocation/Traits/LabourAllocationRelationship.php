<?php

namespace App\Models\labour_allocation\Traits;

use App\Models\branch\Branch;
use App\Models\customer\Customer;
use App\Models\djc\Djc;
use App\Models\quote\Quote;
use App\Models\hrm\Hrm;
use App\Models\labour_allocation\LabourAllocationItem;
use App\Models\project\Project;

/**
 * Class ProductcategoryRelationship
 */
trait LabourAllocationRelationship
{
     public function employee()
     {
        return $this->belongsTo(Hrm::class, 'employee_id');
     }

     public function items()
     {
        return $this->hasMany(LabourAllocationItem::class, 'labour_id');
     }

     public function project()
     {
         return $this->belongsTo(Project::class, 'project_id');
     }
}
