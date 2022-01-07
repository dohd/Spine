<?php

namespace App\Models\stock;

use Illuminate\Database\Eloquent\Model;

class StockIssuedItem extends Model
{
    protected $table = 'stock_issued_items';

    /**
     * Guarded fields of model
     * @var array
     */
    protected $guarded = [
        'id'
    ];
}
