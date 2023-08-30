<?php

namespace App\Models\banktransfer\Traits;

//use App\Models\hrm\Hrm;

/**
 * Class TransactionRelationship
 */
trait BanktransferRelationship
{
    public function account()
    {
        return $this->belongsTo('App\Models\account\Account');
    }
}
