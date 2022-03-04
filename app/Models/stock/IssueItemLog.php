<?php

namespace App\Models\stock;

use App\Models\project\BudgetItem;
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

    public function budget_item()
    {
        return $this->belongsTo(BudgetItem::class, 'item_id');
    }
}
