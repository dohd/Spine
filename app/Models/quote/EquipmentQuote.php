<?php

namespace App\Models\quote;

use App\Models\quote\Quote;
use Illuminate\Database\Eloquent\Model;

class EquipmentQuote extends Model
{
    protected $table = 'quote_equipment';

    protected $fillable = [];

    public $timestamps = false;

    /**
     * Relations
     */
    public function quote()
    {
        return $this->belongsTo(Quote::class);
    }
}
