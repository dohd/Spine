<?php

namespace App\Models\employeeDailyLog;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EmployeeTasks extends Model
{

    use SoftDeletes;

    protected $table = 'employee_tasks';

    protected $primaryKey = 'et_number';

    public $incrementing = false;

    protected $keyType = 'string';


    protected $fillable = [
        'edl_number',
        'category',
        'description',
        'hours',
    ];

}
