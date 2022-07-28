<?php

namespace App\Models\customer\Traits;

use App\Models\branch\Branch;
use App\Models\lead\Lead;
use App\Models\transaction\Transaction;
use App\Models\project\Project;


/**
 * Class CustomerRelationship
 */
trait CustomerRelationship
{
    public function leads()
    {
        return $this->hasMany(Lead::class, 'client_id');
    }

    public function branches()
    {
        return $this->hasMany(Branch::class);
    }

    public function group()
    {
        return $this->hasMany('App\Models\customergroup\CustomerGroupEntry');
    }

    public function primary_group()
    {
        return $this->hasOne('App\Models\customergroup\CustomerGroupEntry')->oldest();
    }

    public function invoices()
    {
        return $this->hasMany('App\Models\invoice\Invoice')->orderBy('id', 'DESC');
    }

    public function amount()
    {
        return $this->hasMany(Transaction::class, 'payer_id');
    }

    public function project()
    {
        return $this->belongsTo(Project::class);
    }
}
