<?php

namespace App\Models\note\Traits;

use App\Models\hrm\Hrm;
use App\Models\project\Project;

/**
 * Class NoteRelationship
 */
trait NoteRelationship
{
    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function creator()
    {
        return $this->hasOne(Hrm::class, 'id', 'user_id');
    }
}
