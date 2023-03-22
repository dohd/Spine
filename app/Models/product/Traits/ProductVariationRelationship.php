<?php

namespace App\Models\product\Traits;

use App\Models\items\ProjectstockItem;
use App\Models\items\PurchaseItem;
use App\Models\items\PurchaseorderItem;
use App\Models\items\QuoteItem;
use App\Models\product\Product;
use App\Models\product\ProductMeta;
use App\Models\warehouse\Warehouse;
use App\Models\pricegroup\Pricegroup;
use App\Models\pricegroup\PriceGroupVariation;

/**
 * Class ProductRelationship
 */
trait ProductVariationRelationship
{
    public function quote_item()
    {
        return $this->hasOne(QuoteItem::class, 'product_id');
    }

    public function purchase_item()
    {
        return $this->hasOne(PurchaseItem::class, 'item_id')->where('type', 'Stock');
    }

    public function purchaseorder_item()
    {
        return $this->hasOne(PurchaseorderItem::class, 'item_id');
    }

    public function project_stock_item()
    {
        return $this->hasOne(ProjectstockItem::class, 'product_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'parent_id');
    }

    public function quote_service_items()
    {
        return $this->belongsTo(Product::class, 'parent_id')->where('stock_type', 'service');
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function product_serial()
    {
        return $this->hasMany(ProductMeta::class, 'ref_id', 'id')->where('rel_type', '=', 2)->withoutGlobalScopes();
    }

    public function v_prices()
    {
        return $this->hasOne(PriceGroupVariation::class, 'product_variation_id', 'id');
    }

    public function variation_price()
    {
        return $this->hasOneThrough(Pricegroup::class, PriceGroupVariation::class, 'product_variation_id', 'pricegroup_id');
    }
}
