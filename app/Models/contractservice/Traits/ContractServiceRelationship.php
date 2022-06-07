<?php

namespace App\Models\contractservice\Traits;

use App\Models\contract\Contract;
use App\Models\items\ServiceItem;
use App\Models\task_schedule\TaskSchedule;

trait ContractServiceRelationship
{
    public function contract()
    {
        return $this->belongsTo(Contract::class);
    }

    public function task_schedule()
    {
        return $this->belongsTo(TaskSchedule::class, 'schedule_id');
    }

    public function items()
    {
        return $this->hasMany(ServiceItem::class, 'service_id');
    }
}
