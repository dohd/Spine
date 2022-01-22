<?php

namespace App\Models\project;

use Illuminate\Database\Eloquent\Model;

class BudgetItem extends Model
{
    /**
     * Guarded fields of model
     * @var array
     */
    protected $guarded = [
        'id'
    ];

    // scope
    public function scopeOrderByRow($query)
    {
        return $query->orderBy('row_index', 'asc');
    }
}
