<?php

namespace App\Models\items;

use App\Models\items\Traits\IssuanceItemRelationship;
use Illuminate\Database\Eloquent\Model;

class IssuanceItem extends Model
{
    use IssuanceItemRelationship;

    /**
     * The database table used by the model.
     * @var string
     */
    protected $table = 'issuance_items';

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
     * Guarded fields of model
     * @var array
     */
    protected $guarded = [
        'id'
    ];
}