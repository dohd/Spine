<?php

namespace App\Models\lead\Traits;

use App\Models\branch\Branch;
use App\Models\customer\Customer;
use App\Models\djc\Djc;
use App\Models\quote\Quote;

/**
 * Class ProductcategoryRelationship
 */
trait LeadRelationship
{
     public function djcs()
     {
          return $this->hasMany(Djc::class);
     }

     public function quotes() 
     {
          return $this->hasMany(Quote::class);
     }

     public function branch()
     {
          return $this->belongsTo(Branch::class, 'branch_id');
     }

     public function customer()
     {
          return $this->belongsTo(Customer::class, 'client_id');
     }
}
