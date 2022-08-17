<?php
/*
 * Rose Business Suite - Accounting, CRM and POS Software
 * Copyright (c) UltimateKode.com. All Rights Reserved
 * ***********************************************************************
 *
 *  Email: support@ultimatekode.com
 *  Website: https://www.ultimatekode.com
 *
 *  ************************************************************************
 *  * This software is furnished under a license and may be used and copied
 *  * only  in  accordance  with  the  terms  of such  license and with the
 *  * inclusion of the above copyright notice.
 *  * If you Purchased from Codecanyon, Please read the full License from
 *  * here- http://codecanyon.net/licenses/standard/
 * ***********************************************************************
 */

namespace App\Http\Controllers\Focus\product;

use App\Models\product\Product;
use App\Models\product\ProductVariation;
use App\Models\productcategory\Productcategory;
use App\Models\warehouse\Warehouse;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Responses\RedirectResponse;
use App\Http\Responses\ViewResponse;
use App\Http\Responses\Focus\product\CreateResponse;
use App\Http\Responses\Focus\product\CreateModalResponse;
use App\Http\Responses\Focus\product\EditResponse;
use App\Repositories\Focus\product\ProductRepository;
use App\Http\Requests\Focus\product\ManageProductRequest;
use App\Http\Requests\Focus\product\CreateProductRequest;
use App\Http\Requests\Focus\product\EditProductRequest;
use App\Models\items\PurchaseItem;
use App\Models\pricegroup\Pricegroup;
use App\Models\productvariable\Productvariable;
use Illuminate\Support\Facades\DB;

/**
 * ProductsController
 */
class ProductsController extends Controller
{
    /**
     * variable to store the repository object
     * @var ProductRepository
     */
    protected $repository;

    /**
     * contructor to initialize repository object
     * @param ProductRepository $repository ;
     */
    public function __construct(ProductRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Display a listing of the resource.
     *
     * @param App\Http\Requests\Focus\product\ManageProductRequest $request
     * @return \App\Http\Responses\ViewResponse
     */
    public function index(ManageProductRequest $request)
    {
        $input = $request->only('rel_type', 'rel_id');
        $segment = false;
        if (isset($input['rel_id']) and isset($input['rel_type'])) {
            switch ($input['rel_type']) {
                case 2:
                    $segment = Warehouse::find($input['rel_id']);
                    break;
                default:
                    $segment = Productcategory::find($input['rel_id']);
            }
        }
        return new ViewResponse('focus.products.index', compact('input', 'segment'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @param CreateProductRequestNamespace $request
     * @return \App\Http\Responses\Focus\product\CreateResponse
     */
    public function create(CreateProductRequest $request)
    {
        return new CreateResponse('focus.products.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param StoreProductRequestNamespace $request
     * @return \App\Http\Responses\RedirectResponse
     */
    public function store(CreateProductRequest $request)
    {
        $this->repository->create($request->except(['_token']));

        return new RedirectResponse(route('biller.products.index'), ['flash_success' => trans('alerts.backend.products.created')]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param App\Models\product\Product $product
     * @param EditProductRequestNamespace $request
     * @return \App\Http\Responses\Focus\product\EditResponse
     */
    public function edit(Product $product, EditProductRequest $request)
    {
        return new EditResponse($product);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param UpdateProductRequestNamespace $request
     * @param App\Models\product\Product $product
     * @return \App\Http\Responses\RedirectResponse
     */
    public function update(EditProductRequest $request, Product $product)
    {
        $this->repository->update($product, $request->except(['_token']));
        
        return new RedirectResponse(route('biller.products.index'), ['flash_success' => trans('alerts.backend.products.deleted')]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param \App\Models\product\Product $product
     * @return \App\Http\Responses\RedirectResponse
     */
    public function destroy(Product $product)
    {
        $this->repository->delete($product);

        return json_encode(array('status' => 'Success', 'message' => trans('alerts.backend.products.deleted')));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param DeleteProductRequestNamespace $request
     * @param App\Models\product\Product $product
     * @return \App\Http\Responses\RedirectResponse
     */
    public function show(Product $product, ManageProductRequest $request)
    {
        return new ViewResponse('focus.products.view', compact('product'));
    }

    /**
     * Quote or PI searchable product drop down options
     */
    public function quote_product_search(Request $request)
    {
        if (!access()->allow('product_search')) return false;

        $pricegroup = Pricegroup::whereHas('pricelist')->find($request->pricegroup_id);

        $productvariations = ProductVariation::whereHas('product', function ($q) {
            $q->where('name', 'LIKE', '%' . request('keyword') . '%');
        })->with(['warehouse' => function ($q) {
            $q->select(['id', 'title']);
        }])->with('product')->limit(6)->get();

        // modify price properties of products
        $products = array();
        foreach ($productvariations as $row) {
            $product = $row;
            $product = array_intersect_key($product->toArray(), 
                array_flip(['id', 'product_id', 'name', 'code', 'qty', 'image', 'purchase_price', 'price', 'alert'])
            );
            $product = $product + [
                'product_des' => $row->product->product_des,
                'units' => $row->product->units,
                'warehouse' => $row->warehouse->toArray()
            ];
            
            // set purchase price using LIFO algorithm
            $purchase_price = $this->repository->compute_purchase_price($row->id, $row->qty, $row->purchase_price);
            $product['purchase_price'] = numberFormat($purchase_price);      

            // set customer selling price or supplier purchase price
            if ($pricegroup) {
                foreach ($pricegroup->pricelist as $item) {
                    if ($item->product_id == $row->product_id) {
                        $product['name'] = $item->name;
                        if ($pricegroup->is_client) $product['price'] = numberFormat($item->price);                            
                        else $product['purchase_price'] = numberFormat($item->price);
                        $products[] =  $product;
                    }
                }
                continue;
            }
            $products[] =  $product;
        }
        
        return response()->json($products);
    }

    public function product_sub_load(Request $request)
    {
        $q = $request->get('id');
        $result = \App\Models\productcategory\Productcategory::all()->where('c_type', '=', 1)->where('rel_id', '=', $q);

        return json_encode($result);
    }

    public function quick_add(CreateProductRequest $request)
    {
        return new CreateModalResponse('focus.modal.product');
    }
}
