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
        $core = $this->repository->getForDataTable();

        // aggregate
        $product_count = 0;
        $product_worth = 0;
        foreach ($core as $product) {
            $std_product = $product->standard;
            if ($std_product && $std_product->qty) {
                $product_count++;
                $product_worth += $std_product->purchase_price;
            }
        }
        $product_worth = amountFormat($product_worth);
        $aggregate = compact('product_count', 'product_worth');
       
        return Datatables::of($core)
            ->escapeColumns(['id'])
            ->addIndexColumn()
            ->addColumn('name', function ($product) {
                $this->standard_product = $product->standard ?: $product;
                return '<a class="font-weight-bold" href="' . route('biller.products.show', [$product->id]) . '">' . $product->name . '</a>';
            })
            ->addColumn('code', function ($product) {
                return  $this->standard_product->code;
            })
            ->addColumn('qty', function ($product) {
                return $product->variations->sum('qty');       
            })
            ->addColumn('unit', function ($product) {
                $unit = $this->standard_product->unit;
                if ($unit) return $unit->code;  
            })
            ->addColumn('price', function ($product) {
                return NumberFormat($this->standard_product->purchase_price);
            })
            ->addColumn('created_at', function ($product) {
                return $product->created_at->format('d-m-Y');
            })
            ->addColumn('aggregate', function ($product) use($aggregate) {
                return $aggregate;
            })
            ->addColumn('actions', function ($product) {
                $buttons = '';
                if ($product->action_buttons) $buttons = $product->action_buttons;
                if (isset($product->product->action_buttons)) $buttons = $product->product->action_buttons;

                return $buttons;
            })
            ->make(true);
    }
}
