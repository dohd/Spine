<?php

namespace App\Models\note\Traits;

use App\Models\hrm\Hrm;
use App\Models\project\Project;
use App\Models\project\ProjectRelations;

/**
 * Class NoteRelationship
 */
trait NoteRelationship
{
    public function project()
    {
        return $this->hasOneThrough(Project::class, ProjectRelations::class, 'note_id', 'id', 'id', 'project_id');
    }

    public function creator()
    {
        return $this->hasOne(Hrm::class, 'id', 'user_id');
    }
}
