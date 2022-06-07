<?php

namespace App\Models\items\Traits;

use App\Models\contract_service\ContractService;
use App\Models\equipment\Equipment;

trait ServiceItemRelationship
{
    public function contract_service()
    {
        return $this->belongsTo(ContractService::class, 'service_id');
    }

    public function  equipment()
    {
        return $this->belongsTo(Equipment::class);
    }
}
