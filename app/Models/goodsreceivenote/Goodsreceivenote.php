<?php

namespace App\Models\goodsreceivenote;

use App\Models\goodsreceivenote\Traits\GoodsreceivenoteAttribute;
use App\Models\goodsreceivenote\Traits\GoodsreceivenoteRelationship;
use App\Models\ModelTrait;
use Illuminate\Database\Eloquent\Model;


class Goodsreceivenote extends Model
{
    use ModelTrait, GoodsreceivenoteAttribute, GoodsreceivenoteRelationship;

    /**
     * NOTE : If you want to implement Soft Deletes in this model,
     * then follow the steps here : https://laravel.com/docs/5.4/eloquent#soft-deleting
     */

    /**
     * The database table used by the model.
     * @var string
     */
    protected $table = 'goods_receive_notes';

    /**
     * Mass Assignable fields of model
     * @var array
     */
    protected $fillable = ['tid', 'supplier_id', 'purchaseorder_id', 'date', 'note', 'dnote', 'user_id', 'ins'];

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
    protected static function boot()
    {
        parent::boot();
        static::addGlobalScope('ins', function ($builder) {
            $builder->where('ins', '=', auth()->user()->ins);
        });
    }
}
