<?php

namespace App\Models\hrm\Traits;

trait AttendanceRelationship
{
    public function employee()
    {
        return $this->belongsTo(User::class, 'employee_id');
    }
}
