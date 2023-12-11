<?php

namespace App\Models\employeeDailyLog;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class EmployeeDailyLog extends Model
{

    use SoftDeletes;

    protected $table = 'employee_daily_logs';

    protected $primaryKey = 'edl_number';

    public $incrementing = false;

    protected $keyType = 'string';


    protected $fillable = [
        'date',
        'rating',
        'remarks',
        'reviewer',
        'reviewed_at',
    ];


    public function tasks(): HasMany {

        return $this->hasMany(EmployeeTasks::class, 'edl_number', 'edl_number');
    }


}
