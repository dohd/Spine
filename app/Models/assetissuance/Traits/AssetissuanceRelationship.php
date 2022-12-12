<?php

namespace App\Models\assetissuance\Traits;

use App\Models\assetissuance\AssetissuanceItems;

/**
 * Class AssetissuanceorderRelationship
 */
trait AssetissuanceRelationship
{
   

    public function items()
    {
        return $this->hasMany(AssetissuanceItems::class, 'item_id','id');
    }

    public function item()
    {
        return $this->hasMany(AssetissuanceItems::class, 'asset_issuance_id','id');
    }
 }
