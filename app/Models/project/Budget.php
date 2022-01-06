<?php

namespace App\Models\project;

use Illuminate\Database\Eloquent\Model;

class Budget extends Model
{
    protected $table = 'budgets';

    /**
     * Guarded fields of model
     * @var array
     */
    protected $guarded = [
        'id'
    ];
}
