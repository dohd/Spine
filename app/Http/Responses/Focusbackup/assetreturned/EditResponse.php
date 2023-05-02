<?php

namespace App\Http\Responses\Focus\assetreturned;

use App\Models\additional\Additional;
use App\Models\pricegroup\Pricegroup;
use App\Models\supplier\Supplier;
use App\Models\assetreturned\AssetreturnedItems;
use App\Models\warehouse\Warehouse;
use Illuminate\Contracts\Support\Responsable;

class EditResponse implements Responsable
{
    /**
     * @var App\Models\assetreturnedorder\assetreturnedorder
     */
    protected $assetreturned;

    /**
     * @param App\Models\assetreturnedorder\assetreturnedorder $assetreturnedorders
     */
    public function __construct($assetreturned)
    {
        $this->assetreturned = $assetreturned;
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
        $assetreturned = $this->assetreturned;
        
        $additionals = Additional::all();
        $pricegroups = Pricegroup::all();
        $warehouses = Warehouse::all();
        $supplier = Supplier::where('name', 'Walk-in')->first(['id', 'name']);
        $price_supplier = Supplier::all();
        $assetreturned_items = AssetreturnedItems::where('asset_returned_id',$assetreturned->id)->get();
        //dd($assetreturned_items);
        return view('focus.assetreturned.edit', compact('assetreturned', 'additionals', 'pricegroups','price_supplier', 'warehouses','assetreturned_items'));
    }
}
