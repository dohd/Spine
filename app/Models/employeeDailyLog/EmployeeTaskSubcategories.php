<?php

namespace App\Models\employeeDailyLog;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class EmployeeTaskSubcategories extends Model
{

    use SoftDeletes;

    protected $table = 'employee_task_subcategories';

    protected $fillable = [
        'id',
        'name',
        'department',
        'frequency'
    ];

    public function employeeTasks(): HasMany {

        return $this->hasMany(EmployeeTasks::class, 'subcategory', 'id');
    }


}
