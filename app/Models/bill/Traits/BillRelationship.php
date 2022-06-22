<?php

namespace App\Models\bill\Traits;

use App\Models\billitem\BillItem;
use App\Models\items\PaidbillItem;
use App\Models\purchaseorder\Purchaseorder;
use App\Models\supplier\Supplier;

/**
 * Class InvoiceRelationship
 */
trait BillRelationship
{
    public function items()
    {
        return $this->hasMany(BillItem::class);
    }

    public function purchaseorder()
    {
        return $this->belongsTo(Purchaseorder::class, 'po_id');
    }

    public function paidbill() 
    {
        return $this->hasOne(PaidbillItem::class);
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function customer()
    {
        return $this->belongsTo('App\Models\customer\Customer')->withoutGlobalScopes();
    }

    public function products()
    {
        return $this->hasMany('App\Models\items\InvoiceItem', 'invoice_id')->withoutGlobalScopes();
    }

    public function user()
    {
        return $this->belongsTo('App\Models\Access\User\User');
    }

    public function term()
    {
        return $this->belongsTo('App\Models\term\Term')->withoutGlobalScopes();
    }

    public function transactions()
    {
        return $this->hasMany('App\Models\transaction\Transaction', 'tr_ref')->where('tr_type', 'bill');
    }
}
