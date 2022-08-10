<?php

namespace App\Models\purchaseorder\Traits;

use App\Models\bill\Bill;
use App\Models\items\GrnItem;
use App\Models\items\PurchaseorderItem;
use App\Models\project\Project;
use App\Models\purchaseorder\Grn;
use App\Models\transaction\Transaction;

/**
 * Class PurchaseorderRelationship
 */
trait PurchaseorderRelationship
{
    public function goods()
    {
        return $this->hasMany(PurchaseorderItem::class, 'purchaseorder_id');
    }

    public function grn_items()
    {
        return $this->hasManyThrough(GrnItem::class, Grn::class, 'purchaseorder_id', 'grn_id')->withoutGlobalScopes();
    }

    public function grn()
    {
        return $this->hasMany(Grn::class, 'purchaseorder_id');
    }

    public function items()
    {
        return $this->hasMany(PurchaseorderItem::class);
    }

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function customer()
    {
        return $this->belongsTo('App\Models\supplier\Supplier', 'supplier_id')->withoutGlobalScopes();
    }

    public function supplier()
    {
        return $this->belongsTo('App\Models\supplier\Supplier')->withoutGlobalScopes();
    }

    public function products()
    {
        return $this->hasMany('App\Models\items\PurchaseorderItem')->withoutGlobalScopes();
    }

    public function user()
    {
        return $this->belongsTo('App\Models\Access\User\User')->withoutGlobalScopes();
    }

    public function term()
    {
        return $this->belongsTo('App\Models\term\Term')->withoutGlobalScopes();
    }
    
    public function transactions()
    {
        return $this->hasManyThrough(Transaction::class, Bill::class, 'po_id', 'tr_ref')->where('tr_type', 'bill')->withoutGlobalScopes();
    }

    public function attachment()
    {
        return $this->hasMany('App\Models\items\MetaEntry', 'rel_id')->where('rel_type', '=', 9)->withoutGlobalScopes();
    }
}
