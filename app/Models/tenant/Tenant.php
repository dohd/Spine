<?php

namespace App\Models\tenant;

use App\Models\ModelTrait;
use App\Models\tenant\Traits\TenantAttribute;
use App\Models\tenant\Traits\TenantRelationship;
use Illuminate\Database\Eloquent\Model;

class Tenant extends Model
{
    use ModelTrait, TenantAttribute, TenantRelationship;

    /**
     * NOTE : If you want to implement Soft Deletes in this model,
     * then follow the steps here : https://laravel.com/docs/5.4/eloquent#soft-deleting
     */

    /**
     * The database table used by the model.
     * @var string
     */
    protected $table = 'companies';

    /**
     * Mass Assignable fields of model
     * @var array
     */
    protected $fillable = [];

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
        
        static::creating(function ($instance) {
            $instance->fill([
                'main_date_format' => 'd-m-Y',
                'user_date_format' => 'DD-MM-YYYY',
                'zone' => 'US/Central',
                'lang' => 'english',
                'valid' => 1,
                'tax' => 0,
                'currency' => 'E',
                'currency_format' => 0,
            ]);
            return $instance;
        });
    }
}
