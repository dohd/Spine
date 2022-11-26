<?php

namespace App\Models\project\Traits;

use App\Models\project\BudgetItem;
use App\Models\project\BudgetSkillset;

/**
 * Class ProjectRelationship
 */
trait BudgetRelationship
{
    public function items()
    {
        return $this->hasMany(BudgetItem::class);
    }
    
    public function skillsets()
    {
        return $this->hasMany(BudgetSkillset::class);
    }
}
