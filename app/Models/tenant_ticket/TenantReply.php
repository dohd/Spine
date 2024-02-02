<?php

namespace App\Models\tenant_ticket;

use App\Models\tenant_ticket\Traits\TenantReplyRelationship;
use Illuminate\Database\Eloquent\Model;

class TenantReply extends Model
{
    use TenantReplyRelationship;
    
    /**
     * NOTE : If you want to implement Soft Deletes in this model,
     * then follow the steps here : https://laravel.com/docs/5.4/eloquent#soft-deleting
     */

    /**
     * The database table used by the model.
     * @var string
     */
    protected $table = 'tenant_replies';

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
                'index' => TenantReply::max('index')+1,
                'date' => date('Y-m-d'),
                'category' => auth()->user()->business->is_main? 'Operator' : 'Owner',
                'ins' => auth()->user()->ins,
                'user_id' => auth()->user()->id,
            ]);
            return $instance;
        });
    }
}
