<?php

namespace App\Models\task_schedule\Traits;

use App\Models\contract\Contract;
use App\Models\contract_equipment\ContractEquipment;
use App\Models\contractservice\ContractService;
use App\Models\equipment\Equipment;

trait TaskScheduleRelationship
{    
    public function contractservice()
    {
        return $this->hasOne(ContractService::class, 'schedule_id');
    }

    public function contract_equipment()
    {
        return $this->hasMany(ContractEquipment::class, 'schedule_id');
    }

    public function equipments()
    {
        return $this->belongsToMany(Equipment::class, 'contract_equipment', 'schedule_id', 'equipment_id');
    }

    public function contract()
    {
        return $this->belongsTo(Contract::class)->withoutGlobalScopes();
    }
}
