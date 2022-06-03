<?php

namespace App\Models\task_schedule\Traits;

use App\Models\contract\Contract;

trait TaskScheduleRelationship
{    
    public function contract()
    {
        return $this->belongsTo(Contract::class)->withoutGlobalScopes();
    }
}
