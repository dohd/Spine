<?php

namespace App\Models\billpayment;

use App\Models\billpayment\Traits\BillpaymentAtrribute;
use App\Models\billpayment\Traits\BillpaymentRelationship;
use App\Models\ModelTrait;
use Illuminate\Database\Eloquent\Model;


class Billpayment extends Model
{
    use ModelTrait, BillpaymentAtrribute, BillpaymentRelationship;

    /**
     * NOTE : If you want to implement Soft Deletes in this model,
     * then follow the steps here : https://laravel.com/docs/5.4/eloquent#soft-deleting
     */

    /**
     * The database table used by the model.
     * @var string
     */
    protected $table = 'bill_payments';

    /**
     * Mass Assignable fields of model
     * @var array
     */
    protected $fillable = [
        'tid', 'account_id', 'supplier_id', 'employee_id', 'date', 'amount', 'allocate_ttl', 
        'note', 'reference', 'payment_mode'
    ];

    /**
     * Default values for model fields
     * @var array
     */
    protected $attributes = [];

    /**
     * Dates
     * @var array
     */
    protected $dates = [
        'created_at',
        'updated_at'
    ];

    /**
     * Guarded fields of model
     * @var array
     */
    protected $guarded = [
        'id'
    ];

    /**
     * Constructor of Model
     * @param array $attributes
     */
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
    }

    /**
     * model life cycle event listeners
     * @return void
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($instance) {
            $instance->user_id = auth()->user()->id;
            $instance->ins = auth()->user()->ins;
            return $instance;
        });

        static::addGlobalScope('ins', function ($builder) {
            $builder->where('ins', '=', auth()->user()->ins);
        });
    }
}
