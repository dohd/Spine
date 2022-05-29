<?php

namespace App\Http\Responses\Focus\purchase;

use App\Models\additional\Additional;
use App\Models\pricegroup\Pricegroup;
use App\Models\purchase\Purchase;
use App\Models\supplier\Supplier;
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
        $last_tid = Purchase::max('tid');
        $supplier = Supplier::where('name', 'Walk-in')->first(['id', 'name']);
        $pricegroups = Pricegroup::all();

        return view('focus.purchases.create', compact('last_tid', 'additionals', 'supplier', 'pricegroups'));
    }
}
