<?php

namespace App\Http\Responses\Focus\product;

use App\Models\productcategory\Productcategory;
use App\Models\productvariable\Productvariable;
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
        $product_categories = Productcategory::all();
        $warehouses = Warehouse::all();
        $productvariables = Productvariable::all();
        
        return view('focus.products.create')->with(compact('product_categories', 'productvariables', 'warehouses'));
    }
}