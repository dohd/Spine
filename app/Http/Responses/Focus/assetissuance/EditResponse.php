<?php

namespace App\Http\Responses\Focus\assetissuance;

use App\Models\additional\Additional;
use App\Models\pricegroup\Pricegroup;
use App\Models\supplier\Supplier;
use App\Models\assetissuance\AssetissuanceItems;
use App\Models\warehouse\Warehouse;
use Illuminate\Contracts\Support\Responsable;

class EditResponse implements Responsable
{
    /**
     * @var App\Models\assetissuance\assetissuance
     */
    protected $assetissuance;

    /**
     * @param App\Models\assetissuance\assetissuance $assetissuance
     */
    public function __construct($assetissuance)
    {
        $this->assetissuance = $assetissuance;
    }

    /**
     * To Response
     *
     * @param \App\Http\Requests\Request $request
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function toResponse($request)
    {
        $assetissuance = $this->assetissuance;
        
        $additionals = Additional::all();
        $pricegroups = Pricegroup::all();
        $warehouses = Warehouse::all();
        $supplier = Supplier::where('name', 'Walk-in')->first(['id', 'name']);
        $price_supplier = Supplier::all();
        $assetissuance_items = AssetissuanceItems::where('asset_issuance_id',$assetissuance->id)->get();
        //dd($assetissuance_items);
        return view('focus.assetissuance.edit', compact('assetissuance', 'additionals', 'pricegroups','price_supplier', 'warehouses','assetissuance_items'));
    }
}
