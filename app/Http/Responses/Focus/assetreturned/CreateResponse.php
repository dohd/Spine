<?php

namespace App\Http\Responses\Focus\assetreturned;

use App\Models\additional\Additional;
use App\Models\pricegroup\Pricegroup;
use App\Models\assetreturned\assetreturned;
use App\Models\supplier\Supplier;
use App\Models\warehouse\Warehouse;
use App\Models\hrm\Hrm;
use Illuminate\Contracts\Support\Responsable;

class CreateResponse implements Responsable
{
    /**
     * To Response
     *
     * @param \App\Http\Requests\Request $request
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function toResponse($request)
    {
        $additionals = Additional::all();
        $pricegroups = Pricegroup::all();
        $warehouses = Warehouse::all();
        $users = Hrm::all();
        //$last_tid = assetreturned::max('tid');
        $supplier = Supplier::where('name', 'Walk-in')->first(['id', 'name']);
        $price_supplier = Supplier::whereHas('products')->get(['id', 'name']);

        return view('focus.assetreturned.create', compact('additionals', 'supplier', 'pricegroups', 'warehouses','price_supplier','users'));
    }
}
