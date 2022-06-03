<?php

namespace App\Models\task_schedule;

use App\Models\ModelTrait;
use App\Models\project\Traits\TaskRelationship;
use App\Models\task_schedule\Traits\TaskScheduleAttribute;
use Illuminate\Database\Eloquent\Model;

class TaskSchedule extends Model
{
    use ModelTrait, TaskScheduleAttribute, TaskRelationship;
    /**
     * The database table used by the model.
     * @var string
     */
    protected $table = 'task_schedules';

    /**
     * Mass Assignable fields of model
     * @var array
     */
    protected $fillable = [];

    /**
     * Default values for model fields
     * @var array
     */
    protected $attributes = [];

    /**
     * Dates
     * @var array
     */
    protected $dates = [
        'created_at',
        'updated_at'
    ];

    /**
     * Guarded fields of model
     * @var array
     */
    protected $guarded = [
        'id'
    ];    
}
