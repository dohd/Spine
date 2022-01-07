<?php

namespace App\Models\lpo;

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
}
