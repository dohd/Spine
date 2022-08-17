<?php

namespace App\Models\product;

use Illuminate\Database\Eloquent\Model;
use App\Models\product\Traits\ProductVariationRelationship;

class ProductVariation extends Model
{
    use ProductVariationRelationship;
    
    protected $table = 'product_variations';

    /**
     * Mass Assignable fields of model
     * @var array
     */
    protected $fillable = [
        'parent_id', 'name', 'warehouse_id', 'code', 'price', 'purchase_price', 'disrate', 'qty',
        'alert', 'image', 'barcode', 'expiry', 'ins'
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
            $instance->ins = auth()->user()->ins;
            return $instance;
        });

        static::addGlobalScope('ins', function ($builder) {
            $builder->where('ins', '=', auth()->user()->ins);
        });
    }
}
