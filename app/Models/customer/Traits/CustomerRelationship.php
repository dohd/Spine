<?php

namespace App\Models\customer\Traits;

use App\Models\Access\User\User;
use App\Models\branch\Branch;
use App\Models\client_product\ClientProduct;
use App\Models\lead\Lead;
use App\Models\manualjournal\Journal;
use App\Models\transaction\Transaction;
use App\Models\project\Project;
use App\Models\quote\Quote;

/**
 * Class CustomerRelationship
 */
trait CustomerRelationship
{
    public function journal()
    {
        return $this->hasOne(Journal::class, 'customer_id');
    }

    public function user()
    {
        return $this->hasOne(User::class, 'customer_id');
    }
    
    function projects() {
        return $this->hasMany(Project::class);
    }

    function quotes() {
        return $this->hasMany(Quote::class);
    }

    public function products()
    {
        return $this->hasMany(ClientProduct::class);
    }

    public function client_products()
    {
        return $this->hasMany(ClientProduct::class);
    }

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
