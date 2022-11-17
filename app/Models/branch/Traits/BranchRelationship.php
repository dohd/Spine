<?php

namespace App\Models\branch\Traits;

use App\Models\contractservice\ContractService;
use App\Models\customer\Customer;
use App\Models\equipment\Equipment;
use App\Models\lead\Lead;

/**
 * Class ProductcategoryRelationship
 */
trait BranchRelationship
{
    public function contract_services()
    {
        return $this->hasMany(ContractService::class);
    }

    public function equipments()
    {
        return $this->hasMany(Equipment::class);
    }

    public function leads()
    {
        return $this->hasMany(Lead::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
}
