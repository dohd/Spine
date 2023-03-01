<?php

namespace App\Models\project\Traits;

use App\Models\hrm\Hrm;
use App\Models\project\Task;

/**
 * Class ProjectRelationship
 */
trait MileStoneRelationship
{
    public function tasks()
    {
        return $this->hasMany(Task::class, 'milestone_id');
    }

    public function creator()
    {
        return $this->belongsTo(Hrm::class, 'user_id', 'id')->withoutGlobalScopes();;
    }
}
