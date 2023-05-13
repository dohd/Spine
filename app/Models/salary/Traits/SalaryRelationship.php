<?php

namespace App\Models\salary\Traits;

use App\Models\hrm\Hrm;
use App\Models\hrm\HrmMeta;

/**
 * ClasssalaryRelationship
 */
trait SalaryRelationship
{
     
    /**
     * Get the user that owns the SalaryRelationship
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(Hrm::class, 'employee_id', 'id');
    }
}
