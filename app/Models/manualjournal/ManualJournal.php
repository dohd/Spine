<?php

namespace App\Models\manualjournal;

use App\Models\manualjournal\Traits\ManualJournalAttribute;
use App\Models\manualjournal\Traits\ManualJournalRelationship;
use App\Models\ModelTrait;
use Illuminate\Database\Eloquent\Model;


class ManualJournal extends Model
{
    use ModelTrait,
    ManualJournalAttribute,
    ManualJournalRelationship {
        // BankAttribute::getEditButtonAttribute insteadof ModelTrait;
    }
      

    /**
     * NOTE : If you want to implement Soft Deletes in this model,
     * then follow the steps here : https://laravel.com/docs/5.4/eloquent#soft-deleting
     */

    /**
     * The database table used by the model.
     * @var string
     */
    protected $table = 'manual_journals';

    /**
     * Mass Assignable fields of model
     * @var array
     */
    protected $fillable = [

    ];

    /**
     * Default values for model fields
     * @var array
     */
    protected $attributes = [

    ];

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
            static::addGlobalScope('ins', function($builder){
            $builder->where('ins', '=', auth()->user()->ins);
    });
    }
}