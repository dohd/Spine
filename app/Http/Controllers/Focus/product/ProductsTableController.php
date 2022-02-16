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
use App\Repositories\Focus\product\ProductVariationRepository;
use Yajra\DataTables\Facades\DataTables;
use App\Repositories\Focus\product\ProductRepository;
use App\Http\Requests\Focus\product\ManageProductRequest;
use Illuminate\Support\Facades\Storage;

/**
 * Class ProductsTableController.
 */
class ProductsTableController extends Controller
{
    /**
     * variable to store the repository object
     * @var ProductRepository
     */
    protected $product;
    protected $product_variation;

    /**
     * contructor to initialize repository object
     * @param ProductRepository $product ;
     */
    public function __construct(ProductRepository $product, ProductVariationRepository $product_variation)
    {
        $this->product = $product;
        $this->product_variation = $product_variation;
    }

    /**
     * This method return the data of the model
     * @param ManageProductRequest $request
     *
     * @return mixed
     */
    public function __invoke()
    {
        // warehouse products
        if (request('p_rel_id') && request('p_rel_type') == 2) {
            print_log('+++ W/H product +++');
            $core = $this->product_variation->getForDataTable();
        } 
        else $core = $this->product->getForDataTable();
        
        return Datatables::of($core)
            ->escapeColumns(['id'])
            ->addIndexColumn()
            ->addColumn('name', function ($item) {
                return '<a class="font-weight-bold" href="' . route('biller.products.show', [$item->id]) . '">' . $item->name . '</a>';
            })
            ->addColumn('code', function ($item) {
                return  $item->standard ? $item->standard->code : $item->code;
            })
            ->addColumn('warehouse', function ($item) {
                $title = '';
                if (isset($item->standard->warehouse)) $title = $item->standard->warehouse->title;
                if ($item->warehouse) $title = $item->warehouse->title;
                $image = $item->standard ? $item->standard->image : $item->image;

                if ($title && $image)
                    return $title . '<img class="media-object img-lg border" src="'.Storage::disk('public')->url('app/public/img/products/' . $image).'" alt="Product Image">';
            })
            ->addColumn('category', function ($item) {
                $title = '';
                if (isset($item->category)) $title = $item->category->title;
                if (isset($item->product->category)) $title = $item->product->category->title;

                return $title;
            })
            ->addColumn('qty', function ($item) {
                return $item->standard ? intval($item->standard->qty) : intval($item->qty);
            })
            ->addColumn('created_at', function ($item) {
                return dateFormat($item->created_at);
            })
            ->addColumn('price', function ($item) {
                return $item->standard ? amountFormat($item->standard->price) : amountFormat($item->price);
            })
            ->addColumn('actions', function ($item) {
                $buttons = '';
                if ($item->action_buttons) $buttons = $item->action_buttons;
                if (isset($item->product->action_buttons)) $buttons = $item->product->action_buttons;

                return $buttons;
            })
            ->make(true);
    }
}
