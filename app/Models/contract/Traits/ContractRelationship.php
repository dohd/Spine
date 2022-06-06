<?php

namespace App\Models\contract\Traits;

use App\Models\contract_equipment\ContractEquipment;
use App\Models\customer\Customer;
use App\Models\task_schedule\TaskSchedule;

trait ContractRelationship
{
    public function task_schedules()
    {
        return $this->hasMany(TaskSchedule::class);
    }

    public function contract_equipments()
    {
        return $this->hasMany(ContractEquipment::class)->where('schedule_id', 0);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
}
