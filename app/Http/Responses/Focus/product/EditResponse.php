<?php

namespace App\Http\Responses\Focus\product;

use App\Models\product\ProductVariation;
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
        $product_categories = Productcategory::all();
        $product_variables = Productvariable::where('type', 0)->get();
        $warehouses = Warehouse::all();

        return view('focus.products.edit', ['product' => $this->product])->with(
            compact('product_categories', 'product_variables', 'warehouses')
        );
    }
}
