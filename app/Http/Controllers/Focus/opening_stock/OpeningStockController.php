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
namespace App\Http\Controllers\Focus\opening_stock;

use App\Http\Controllers\Controller;
use App\Http\Responses\ViewResponse;
use App\Models\product\ProductVariation;
use App\Models\warehouse\Warehouse;
use App\Repositories\Focus\opening_stock\OpeningStockRepository;
use Illuminate\Http\Request;

class OpeningStockController extends Controller
{
    /**
     * variable to store the repository object
     * @var OpeningStockRepository
     */
    protected $repository;

    /**
     * contructor to initialize repository object
     * @param OpeningStockRepository $repository ;
     */
    public function __construct(OpeningStockRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Display a listing of the resource.
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return new ViewResponse('focus.opening_stock.index');
    }

    /**
     * Show the form for creating a new resource.
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $warehouses = Warehouse::get(['id', 'title']);

        return view('focus.opening_stock.create', compact('warehouses'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return \App\Http\Responses\RedirectResponse
     */
    public function store(Request $request)
    {
        $this->repository->create($request->except('_token'));

        return new RedirectResponse(route('biller.opening_stock.index'), ['flash_success' => 'Opening Stock Created Successfully']);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  OpeningStock $opening_stock
     * @return \Illuminate\Http\Response
     */
    public function edit(OpeningStock $opening_stock)
    {
        return view('focus.opening_stock.edit', compact('opening_stock'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  OpeningStock $opening_stock
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, OpeningStock $opening_stock)
    {
        $this->repository->update($opening_stock, $request->except('_token'));

        return new RedirectResponse(route('biller.opening_stock.index'), ['flash_success' => 'Opening Stock Updated Successfully']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  OpeningStock $opening_stock
     * @return \Illuminate\Http\Response
     */
    public function destroy(OpeningStock $opening_stock)
    {
        $this->repository->delete($opening_stock);

        return new RedirectResponse(route('biller.opening_stock.index'), ['flash_success' => 'Opening Stock Deleted Successfully']);
    }


    /**
     * Display the specified resource.
     *
     * @param  OpeningStock $opening_stock
     * @return \Illuminate\Http\Response
     */
    public function show(OpeningStock $opening_stock)
    {
        return view('focus.opening_stock.view', compact('opening_stock'));
    }

    /**
     * Product Variations
     */
    public function product_variation()
    {
        $products = ProductVariation::where([
            'warehouse_id' => request('warehouse_id')
        ])->with('product')->get()->map(function ($v) {
            return [
                'id' => $v->id, 
                'name' => $v->name, 
                'unit' => $v->product->unit? $v->product->unit->code : ''
            ];
        });

        return response()->json($products);
    }
}
