<?php

namespace App\Models\lpo;

use App\Models\branch\Branch;
use App\Models\customer\Customer;
use App\Models\quote\Quote;
use Illuminate\Database\Eloquent\Model;

class Lpo extends Model
{
    /**
     * The database table used by the model.
     * @var string
     */
    protected $table = 'lpos';

    /**
     * Guarded fields of model
     * @var array
     */
    protected $guarded = [
        'id'
    ];

    public function quotes()
    {
        return $this->hasMany(Quote::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }
}
