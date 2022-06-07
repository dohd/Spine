<?php

namespace App\Models\task_schedule\Traits;

use App\Models\contract\Contract;
use App\Models\contract_equipment\ContractEquipment;
use App\Models\contractservice\ContractService;

trait TaskScheduleRelationship
{    
    public function contractservice()
    {
        return $this->hasOne(ContractService::class, 'schedule_id');
    }

    public function taskschedule_equipments()
    {
        return $this->hasMany(ContractEquipment::class, 'schedule_id');
    }

    public function contract()
    {
        return $this->belongsTo(Contract::class)->withoutGlobalScopes();
    }
}
