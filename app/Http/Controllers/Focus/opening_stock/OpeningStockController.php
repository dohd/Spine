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
use App\Http\Responses\RedirectResponse;
use App\Http\Responses\ViewResponse;
use App\Models\opening_stock\OpeningStock;
use App\Models\product\ProductVariation;
use App\Models\warehouse\Warehouse;
use App\Repositories\Focus\opening_stock\OpeningStockRepository;
use DB;
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
        // import opening stock from existing inventory
        // $data = [
        //     'tid' => OpeningStock::max('tid')+1,
        //     'date' => date('Y-m-d'),
        //     'note' => 'Opening Stock Balance',
        //     'warehouse_id' => 10,
        //     'total' => 0,
        // ];
        // $data_items = ProductVariation::select(DB::raw('id as product_id, parent_id, purchase_price, qty, (purchase_price * qty) as amount'))
        //     ->get();

        // $data['total'] = $data_items->sum('amount');
        // $data_items2 = [];
        // foreach ($data_items->toArray() as $row) {
        //     foreach ($row as $i => $value) {
        //         if (isset($data_items2[$i])) $data_items2[$i][] = $value;
        //         else $data_items2[$i] = [$value];
        //     }
        // }

        // $input = array_merge($data, $data_items2);
        // $this->repository->create($input);

        return new ViewResponse('focus.opening_stock.index');
    }

    /**
     * Show the form for creating a new resource.
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $tid = OpeningStock::where('ins', auth()->user()->ins)->max('tid');
        $warehouses = Warehouse::get(['id', 'title']);

        return view('focus.opening_stock.create', compact('warehouses', 'tid'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return \App\Http\Responses\RedirectResponse
     */
    public function store(Request $request)
    {
        try {
            $this->repository->create($request->except('_token'));
        } catch (\Throwable $th) {
            return errorHandler('Error Creating Opening Stock', $th);
        }

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
        $warehouses = Warehouse::get(['id', 'title']);

        return view('focus.opening_stock.edit', compact('opening_stock', 'warehouses'));
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
        try {
            $this->repository->update($opening_stock, $request->except('_token'));
        } catch (\Throwable $th) {
            return errorHandler('Error Updating Opening Stock', $th);
        }

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
        try {
            $this->repository->delete($opening_stock);
        } catch (\Throwable $th) {
            return errorHandler('Error Deleting Opening Stock', $th);
        }

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
        ])->with('product')->get()
        ->map(function ($v) {
            return [
                'id' => $v->id, 
                'name' => $v->name, 
                'parent_id' => $v->product->id,
                'unit' => $v->product->unit? $v->product->unit->code : ''
            ];
        });

        return response()->json($products);
    }
}
