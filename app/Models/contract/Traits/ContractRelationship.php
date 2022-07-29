<?php

namespace App\Models\contract\Traits;

use App\Models\contract_equipment\ContractEquipment;
use App\Models\customer\Customer;
use App\Models\equipment\Equipment;
use App\Models\task_schedule\TaskSchedule;

trait ContractRelationship
{
    public function task_schedules()
    {
        return $this->hasMany(TaskSchedule::class);
    }

    public function contract_equipment() 
    {
        return $this->hasMany(ContractEquipment::class)->whereNull('schedule_id');
    }
    
    public function equipments()
    {
        return $this->belongsToMany(Equipment::class, 'contract_equipment')->whereNull('schedule_id');
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
}
