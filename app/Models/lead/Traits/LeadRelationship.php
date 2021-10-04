<?php

namespace App\Models\lead\Traits;

use App\Models\branch\Branch;
use App\Models\customer\Customer;
use DB;
/**
 * Class ProductcategoryRelationship
 */
trait LeadRelationship
{
    public function branch()
    {
         return $this->belongsTo(Branch::class,'branch_id');
    }

    public function customer()
    {
         return $this->belongsTo(Customer::class,'client_id');
    }


      
}
