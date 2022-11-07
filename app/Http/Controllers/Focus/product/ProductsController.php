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
use App\Models\client_product\ClientProduct;

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
        $warehouses = Warehouse::get(['id', 'title']);
        $categories = Productcategory::get(['id', 'title']);

        return new ViewResponse('focus.products.index', compact('warehouses', 'categories'));
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

        // fetch pricelist customer products
        if ($request->price_customer_id) {
            $products = ClientProduct::where('customer_id', request('price_customer_id'))
                ->where('descr', 'LIKE', '%'. request('keyword') .'%')->limit(6)->get()
                ->map(function ($v) {
                    return $v->fill([
                        'name' => "{$v->descr} ({$v->row_num})",
                        'unit' => $v->uom,
                        'price' => $v->rate,
                        'purchase_price' => 0,
                    ]);
                });

            return response()->json($products);
        }

        // fetch inventory products
        $productvariations = ProductVariation::whereHas('product', function ($q) {
            $q->where('name', 'LIKE', '%' . request('keyword') . '%');
        })->with(['warehouse' => function ($q) {
            $q->select(['id', 'title']);
        }])->with('product')->limit(6)->get()->unique('name');
        
        $products = array();
        foreach ($productvariations as $row) {
            $product = array_intersect_key($row->toArray(), array_flip([
                'id', 'product_id', 'name', 'code', 'qty', 'image', 'purchase_price', 'price', 'alert'
            ]));
            $product = $product + [
                'product_des' => $row->product->product_des,
                'units' => $row->product->units,
                'warehouse' => $row->warehouse->toArray()
            ];
            // purchase price set by inventory valuation (LIFO) method
            $product['purchase_price'] = $this->repository->compute_purchase_price(
                $row->id, $row->qty, $row->purchase_price
            );  
            $products[] =  $product;
        }

        return response()->json($products);
    }

    // 
    public function product_sub_load(Request $request)
    {
        $q = $request->get('id');
        $result = \App\Models\productcategory\Productcategory::all()->where('c_type', '=', 1)->where('rel_id', '=', $q);

        return json_encode($result);
    }

    // 
    public function quick_add(CreateProductRequest $request)
    {
        return new CreateModalResponse('focus.modal.product');
    }

    /**
     * Point of Sale
     */
    public function pos(Request $request, $bill_type)
    {
        if (!access()->allow('pos')) return false;
        $q = $request->post('keyword');
        $w = $request->wid;
        $cat_id = $request->post('cat_id');
        $s = $request->post('serial_mode');
        $limit = $request->post('search_limit', 20);
        $bill_type=$request->bill_type;
        if ($bill_type == 'label'){
            $q = @$request->post('product')['term'];

        }

        $wq = compact('q', 'w', 'cat_id');
        if ($s == 1 and $q) {
            $product = \App\Models\product\ProductMeta::where('value', 'LIKE', '%' . $q . '%')->whereNull('value2')->whereHas('product_serial', function ($query) use ($wq) {
                if ($wq['w'] > 0) return $query->where('warehouse_id', $wq['w']);
            })->with(['product_standard'])->limit($limit)->get();
            $output = array();

            foreach ($product as $row) {

                $output[] = array('name' => $row->product_serial->product['name'], 'disrate' => $row->product_serial['disrate'], 'price' => $row->product_serial['price'], 'id' => $row->product_serial['id'], 'taxrate' => $row->product_serial->product['taxrate'], 'product_des' => $row->product_serial->product['product_des'], 'unit' => $row->product_serial->product['unit'], 'code' => $row->product_serial['code'], 'alert' => $row->product_serial['qty'], 'image' => $row->product_serial['image'], 'serial' => $row->value);


            }
        } else {

            $product = ProductVariation::whereHas('product', function ($query) use ($wq) {
                $query->where('name', 'LIKE', '%' . $wq['q'] . '%');
                if ($wq['cat_id'] > 0) $query->where('productcategory_id', $wq['cat_id']);
                return $query;
            })->when($wq['w'] > 0, function ($q) use ($wq) {
                $q->where('warehouse_id', $wq['w']);
            })->limit($limit)->get();
            $output = array();

            foreach ($product as $row) {
                if (($row->product->stock_type > 0 and $row->qty > 0) or !$row->product->stock_type) {
                    $output[] = array('name' => $row->product->name . ' ' . $row['name'], 'disrate' => numberFormat($row->disrate), 'price' => numberFormat($row->price), 'id' => $row->id, 'taxrate' => numberFormat($row->product['taxrate']), 'product_des' => $row->product['product_des'], 'unit' => $row->product['unit'], 'code' => $row->code, 'alert' => $row->qty, 'image' => $row->image, 'serial' => '');
                }
            }

        }

        if (count($output) > 0)

            return view('focus.products.partials.pos')->withDetails($output);
    }
}
