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
use App\Models\supplier_product\SupplierProduct;
use App\Models\product\ProductMeta;

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
        try {
            $this->repository->create($request->except(['_token']));
        } catch (\Throwable $th) {
            return errorHandler($th, 'Error Creating Product');
        }

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
        try {
            $this->repository->update($product, $request->except(['_token']));
        } catch (\Throwable $th) {
            return errorHandler($th, 'Error Updating Product');
        }
        
        return new RedirectResponse(route('biller.products.index'), ['flash_success' => trans('alerts.backend.products.updated')]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param \App\Models\product\Product $product
     * @return \App\Http\Responses\RedirectResponse
     */
    public function destroy(Product $product)
    {
        try {
            $this->repository->delete($product);
        } catch (\Throwable $th) {
            return errorHandler($th, 'Error Deleting Product');
        }
        
        return json_encode(['status' => 'Success', 'message' => trans('alerts.backend.products.deleted')]);
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
                    $value = $v->row_num > 0 ? "($v->row_num)" : '';
                    return $v->fill([
                        'name' => "{$v->descr} {$value}",
                        'unit' => $v->uom,
                        'price' => $v->rate,
                        'purchase_price' => 0,
                    ]);
                });

            return response()->json($products);
        }

        // fetch inventory products
        $productvariations = ProductVariation::where(function ($q) {
            $q->whereHas('product', function ($q) {
                $q->where('name', 'LIKE', '%' . request('keyword') . '%');
            })->orWhere('name', 'LIKE', '%' . request('keyword') . '%');
        })
        ->with(['warehouse' => fn($q) => $q->select(['id', 'title'])])
        ->with('product')->limit(6)->get()->unique('name');
        
        $products = [];
        foreach ($productvariations as $row) {
            $product = [];
            foreach ($row->toArray() as $key => $value) {
                $keys = ['id', 'parent_id', 'name', 'code', 'qty', 'image', 'purchase_price', 'price', 'alert'];
                if (in_array($key, $keys)) $product[$key] = $value;
            }
            $product = array_replace($product, [
                'taxrate' => @$row->product->taxrate,
                'product_des' => @$row->product->product_des,
                'units' => @$row->product->units? $row->product->units->toArray() : [],
                'warehouse' => $row->warehouse? $row->warehouse->toArray() : [],
            ]);
            // set purchase price using inventory valuation (LIFO) method
            $product['purchase_price'] = $this->repository->eval_purchase_price($row->id, $row->qty, $row->purchase_price);
            $products[] =  $product;
        }

        return response()->json($products);
    }
    public function purchase_search(Request $request)
    {
        if (!access()->allow('product_search')) return false;

        // fetch pricelist customer products
        if ($request->pricegroup_id) {
            $products = SupplierProduct::where('supplier_id', request('pricegroup_id'))
                ->where('descr', 'LIKE', '%'. request('keyword') .'%')->limit(6)->get()
                ->map(function ($v) {
                    return $v->fill([
                        'name' => $v->row_num > 0? "{$v->descr} {$v->row_num}" : "{$v->descr}",
                        'unit' => $v->uom,
                        'price' => $v->rate,
                        'purchase_price' => $v->rate,
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
            $product['purchase_price'] = $this->repository->eval_purchase_price($row->id, $row->qty, $row->purchase_price);
                
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
        
        $input = $request->except('_token');
        $limit = $request->post('search_limit', 20);
        $bill_type = $request->bill_type ?: $request->type;

        if ($bill_type == 'label' && isset($input['product']['term']))
            $input['keyword'] = $input['product']['term'];

        if ($input['serial_mode'] == 1 && $input['keyword']) {
            $products = ProductMeta::where('value', 'LIKE', '%' . $input['keyword'] . '%')
                ->whereNull('value2')
                ->whereHas('product_serial', function ($q) use ($input) {
                    if ($input['wid'] > 0) $q->where('warehouse_id', $input['wid']);
                })->with(['product_standard'])->limit($limit)->get();

            $output = array();
            foreach ($products as $row) {
                $serial_product = $row->product_serial;
                $stock_product = $serial_product->product;
                $output[] = [
                    'name' => $stock_product->name, 
                    'disrate' => $serial_product->disrate, 
                    'purchase_price' => $this->repository->eval_purchase_price(
                        $stock_product->id, $stock_product->qty, $stock_product->purchase_price
                    ),
                    'price' => $serial_product['price'], 
                    'id' => $serial_product['id'], 
                    'taxrate' => $stock_product['taxrate'], 
                    'product_des' => $stock_product['product_des'], 
                    'units' => $stock_product['units'], 
                    'code' => $serial_product['code'], 
                    'alert' => $serial_product['qty'], 
                    'image' => $serial_product['image'], 
                    'serial' => $row->value,
                ];
            }
        } else {
            $products = ProductVariation::whereHas('product', function ($q) use ($input) {
                $q->where('name', 'LIKE', '%' . $input['keyword'] . '%');
                if ($input['cat_id'] > 0) $q->where('productcategory_id', $input['cat_id']);
            })->when($input['wid'] > 0, function ($q) use ($input) {
                $q->where('warehouse_id', $input['wid']);
            })->limit($limit)->get();

            $output = array();
            foreach ($products as $row) {
                $output[] = [
                    'name' => $row->name ?: $row->product->name,
                    'disrate' => numberFormat($row->disrate),
                    'purchase_price' => $this->repository->eval_purchase_price(
                        $row->id, $row->qty, $row->purchase_price
                    ),
                    'price' => numberFormat($row->price), 
                    'id' => $row->id, 
                    'taxrate' => numberFormat($row->product['taxrate']), 
                    'product_des' => $row->product['product_des'], 
                    'units' => $row->product->units, 
                    'code' => $row->code, 
                    'alert' => $row->qty, 
                    'image' => $row->image, 
                    'serial' => '',
                ];
            }
        }
        
        return view('focus.products.partials.pos')->withDetails($output);
    }
}
