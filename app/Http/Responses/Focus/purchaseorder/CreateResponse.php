<?php

namespace App\Http\Responses\Focus\purchaseorder;

use App\Models\additional\Additional;
use App\Models\pricegroup\Pricegroup;
use App\Models\purchaseorder\Purchaseorder;
use App\Models\supplier\Supplier;
use App\Models\term\Term;
use App\Models\warehouse\Warehouse;
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
        $ins = auth()->user()->ins;
        $prefixes = prefixesArray(['purchase_order'], $ins);
        $last_tid = Purchaseorder::where('ins', $ins)->max('tid');
        $warehouses = Warehouse::all();
        $additionals = Additional::all();
        $pricegroups = Pricegroup::all();
        $supplier = Supplier::where('name', 'Walk-in')->first(['id', 'name']);
        $price_supplier = Supplier::whereHas('products')->get(['id', 'name']);
        // Purchase order
        $terms = Term::where('type', 4)->get();

        return view('focus.purchaseorders.create', compact('last_tid','warehouses', 'additionals', 'pricegroups','price_supplier','price_supplier', 'terms', 'prefixes'));
    }
}
