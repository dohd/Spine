<?php

namespace App\Http\Responses\Focus\product;

use App\Models\productcategory\Productcategory;
use App\Models\productvariable\Productvariable;
use App\Models\warehouse\Warehouse;
use Illuminate\Contracts\Support\Responsable;

class EditResponse implements Responsable
{
    /**
     * @var App\Models\product\Product
     */
    protected $product;

    /**
     * @param App\Models\product\Product $product
     */
    public function __construct($product)
    {
        $this->product = $product;
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
        $productvariables = Productvariable::all();
        $warehouses = Warehouse::all();
        $product_categories = Productcategory::all();
        $compound_units = $this->product->units()->where('unit_type', 'compound')->get();

        return view('focus.products.edit', compact('product_categories', 'productvariables', 'warehouses'))->with([
            'product' => $this->product,
            'compound_unit_ids' => $compound_units->map(function ($v) { return $v->id; })->toArray()
        ]);
    }
}
