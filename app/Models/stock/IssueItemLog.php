<?php

namespace App\Models\stock;

use Illuminate\Database\Eloquent\Model;

class IssueItemLog extends Model
{
    protected $table = 'issue_item_logs';

    /**
     * Guarded fields of model
     * @var array
     */
    protected $guarded = [
        'id'
    ];
}
