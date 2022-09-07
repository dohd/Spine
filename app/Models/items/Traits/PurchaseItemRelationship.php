<?php

namespace App\Models\items\Traits;

use App\Models\account\Account;
use App\Models\assetequipment\Assetequipment;
use App\Models\project\Project;
use App\Models\purchase\Purchase;

/**
 * Class CustomerRelationship
 */
trait PurchaseItemRelationship
{
    public function purchase()
    {
        return $this->belongsTo(Purchase::class, 'bill_id');
    }

    public function asset()
    {
        return $this->belongsTo(Assetequipment::class, 'item_id');
    }

    public function account()
    {
        return $this->belongsTo(Account::class, 'item_id');
    }

    public function project()
    {
        return $this->belongsTo(Project::class, 'itemproject_id');
    }

    public function productvariation() {
        return $this->belongsTo('App\Models\product\ProductVariation', 'item_id');
    }

    public function product()
    {
        return $this->belongsTo('App\Models\product\ProductVariation', 'item_id');
    }

    public function variation()
    {
        return $this->belongsTo('App\Models\product\ProductVariation', 'item_id')->withoutGlobalScopes();
    }
}
