<?php

namespace App\Models\attendance\Traits;

use App\Models\Access\User\User;

trait AttendanceRelationship
{
    public function employee()
    {
        return $this->belongsTo(User::class, 'employee_id');
    }
}
