<?php

namespace App\Models\assetreturned\Traits;

use App\Models\assetreturned\AssetreturnedItems;

/**
 * Class AssetreturnedorderRelationship
 */
trait AssetreturnedRelationship
{
   

    public function items()
    {
        return $this->hasMany(AssetreturnedItems::class, 'item_id','id');
    }

    public function item()
    {
        return $this->hasMany(AssetreturnedItems::class, 'asset_returned_id','id');
    }
 }
