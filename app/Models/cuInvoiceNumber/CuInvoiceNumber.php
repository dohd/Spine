<?php

namespace App\Models\cuInvoiceNumber;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CuInvoiceNumber extends Model
{

    use SoftDeletes;

    protected $primaryKey = 'id';

}
