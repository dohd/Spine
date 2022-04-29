<?php

namespace App\Models\issuance;

use App\Models\issuance\Traits\IssuanceAttribute;
use App\Models\issuance\Traits\IssuanceRelationship;
use App\Models\ModelTrait;
use Illuminate\Database\Eloquent\Model;

class Issuance extends Model
{
    use ModelTrait,
        IssuanceAttribute,
        IssuanceRelationship {
    }

    /**
     * The database table used by the model.
     * @var string
     */
    protected $table = 'issuance';

    /**
     * Mass Assignable fields of model
     * @var array
     */
    protected $fillable = [
        'id','quote_id', 'date', 'note', 'tool_ref', 'subtotal', 'tax', 'total', 
        'ins', 'user_id'
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
    protected static function boot()
    {
        parent::boot();
        static::addGlobalScope('ins', function ($builder) {
            $builder->where('ins', auth()->user()->ins);
        });
    }
}
