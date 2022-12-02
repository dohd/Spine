<?php

namespace App\Models\djc\Traits;

use App\Models\branch\Branch;
use App\Models\customer\Customer;
use App\Models\lead\Lead;

/**
 * Class ProductcategoryRelationship
 */
trait DjcRelationship
{
     public function lead()
     {
          return $this->belongsTo(Lead::class);
     }

     public function client()
     {
          return $this->hasOneThrough(Customer::class, Lead::class, 'id', 'id', 'lead_id', 'client_id')->withoutGlobalScopes();
     }

     public function branch()
     {
          return $this->hasOneThrough(Branch::class, Lead::class, 'id', 'id', 'lead_id', 'branch_id')->withoutGlobalScopes();
     }

     public function items()
     {
          return $this->hasMany('App\Models\items\DjcItem')->withoutGlobalScopes();
     }
}
