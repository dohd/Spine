<?php

namespace App\Models\equipment;

use App\Models\ModelTrait;
use Illuminate\Database\Eloquent\Model;
use App\Models\equipment\Traits\EquipmentAttribute;
use App\Models\equipment\Traits\EquipmentRelationship;

class Equipment extends Model
{
    use ModelTrait,
        EquipmentAttribute,
        EquipmentRelationship {
    }

    /**
     * NOTE : If you want to implement Soft Deletes in this model,
     * then follow the steps here : https://laravel.com/docs/5.4/eloquent#soft-deleting
     */

    /**
     * The database table used by the model.
     * @var string
     */
    protected $table = 'equipments';

    /**
     * Mass Assignable fields of model
     * @var array
     */
    protected $fillable = [
        'tid', 'customer_id', 'branch_id', 'equip_serial', 'unique_id', 'capacity', 'location', 'machine_gas',
        'make_type', 'model', 'equipment_category_id', 'service_rate', 'building', 'floor', 'install_date', 'note','status','pm_duration'
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
