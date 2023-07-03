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

use App\Http\Controllers\Controller;
use Yajra\DataTables\Facades\DataTables;
use App\Repositories\Focus\product\ProductRepository;
use App\Http\Requests\Focus\product\ManageProductRequest;
use DB;

/**
 * Class ProductsTableController.
 */
class ProductsTableController extends Controller
{
    /**
     * variable to store the repository object
     * @var ProductRepository
     */
    protected $repository;

    // standard product
    protected $standard_product;

    /**
     * contructor to initialize repository object
     * @param ProductRepository $repository ;
     */
    public function __construct(ProductRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * This method return the data of the model
     * @param ManageProductRequest $request
     * @return mixed
     */
    public function __invoke()
    {
        $query = $this->repository->getForDataTable();
        $query_1 = clone $query;
        // aggregate
        $product_count = 0;
        $product_worth = 0;
        foreach ($query_1->get() as $product) {
            $product_count += $product->variations()->count();
            $product_worth += $product->variations()->sum(DB::raw('purchase_price*qty'));
        }
        $product_worth = amountFormat($product_worth);
        $aggregate = compact('product_count', 'product_worth');
       
        return Datatables::of($query)
            ->escapeColumns(['id'])
            ->addIndexColumn()
            ->addColumn('name', function ($product) {
                if ($product->standard) {
                    $this->standard_product = $product->standard;
                    $product_name = $product->name == $product->standard->name? $product->name : "{$product->name} ({$product->standard->name})";
                    return '<a class="font-weight-bold" href="' . route('biller.products.show', [$product->id]) . '">' . $product_name . '</a>';
                }
                $this->standard_product = $product->standard ?: $product;
                return '<a class="font-weight-bold" href="' . route('biller.products.show', [$product->id]) . '">' . $product->name . '</a>';
            })
            ->filterColumn('name', function($query, $name) {
                $query->where('name', 'LIKE', "%{$name}%");
            })
            ->addColumn('code', function ($product) {
                return  $this->standard_product->code;
            })
            ->filterColumn('code', function($query, $code) {

                $query->whereHas('variations', fn($q) => $q->where('code', 'LIKE', "%{$code}%"));
            })
            ->addColumn('qty', function ($product) {
                return $product->variations->sum('qty');       
            })
            ->addColumn('unit', function ($product) {
                $unit = $this->standard_product->unit;
                if ($unit) return $unit->code;  
            })
            ->addColumn('purchase_price', function ($product) {
                return NumberFormat($this->standard_product->purchase_price);
            })
            ->addColumn('total', function ($product) {
                $total = 0;
                foreach ($product->variations as $product_var) {
                    $total += $this->standard_product->purchase_price * $product_var->qty;
                }
                return NumberFormat($total);
            })
            ->addColumn('created_at', function ($product) {
                return dateFormat($product->created_at);
            })
            ->orderColumn('created_at', '-created_at $1')
            ->addColumn('actions', function ($product) {
                return $product->action_buttons;
            })
            ->addColumn('aggregate', function () use($aggregate) {
                return $aggregate;
            })
            ->make(true);
    }
}
