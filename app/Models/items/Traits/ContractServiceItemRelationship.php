<?php

namespace App\Models\items\Traits;

use App\Models\contract_service\ContractService;
use App\Models\equipment\Equipment;

trait ContractServiceItemRelationship
{
    public function contractservice()
    {
        return $this->belongsTo(ContractService::class, 'contractservice_id');
    }

    public function  equipment()
    {
        return $this->belongsTo(Equipment::class);
    }
}