<?php

namespace App\Models\task_schedule\Traits;

use App\Models\contract\Contract;
use App\Models\contract_equipment\ContractEquipment;

trait TaskScheduleRelationship
{    
    public function taskschedule_equipments(Type $var = null)
    {
        return $this->hasMany(ContractEquipment::class, 'schedule_id');
    }

    public function contract()
    {
        return $this->belongsTo(Contract::class)->withoutGlobalScopes();
    }
}
