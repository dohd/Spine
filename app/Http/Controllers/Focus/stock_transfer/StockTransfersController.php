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

namespace App\Http\Controllers\Focus\stock_transfer;

use App\Http\Controllers\Controller;
use App\Http\Responses\RedirectResponse;
use App\Http\Responses\ViewResponse;
use App\Models\stock_transfer\StockTransfer;
use App\Models\warehouse\Warehouse;
use App\Repositories\Focus\stock_transfer\StockTransferRepository;
use DB;
use Illuminate\Http\Request;

class StockTransfersController extends Controller
{
    /**
     * variable to store the repository object
     * @var StockTransferRepository
     */
    protected $repository;

    /**
     * contructor to initialize repository object
     * @param StockTransferRepository $repository ;
     */
    public function __construct(StockTransferRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Display a listing of the resource.
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return new ViewResponse('focus.stock_transfers.index');
    }

    /**
     * Show the form for creating a new resource.
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $tid = StockTransfer::max('tid');

        $warehouses = Warehouse::get(['id', 'title', 'extra']);
        
        return view('focus.stock_transfers.create', compact('tid', 'warehouses'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return \App\Http\Responses\RedirectResponse
     */
    public function store(Request $request)
    {
        $this->repository->create($request->except('_token'));

        return new RedirectResponse(route('biller.stock_transfers.index'), ['flash_success' => 'Stock Transfer Created Successfully']);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  StockTransfer $stock_transfer
     * @return \Illuminate\Http\Response
     */
    public function edit(StockTransfer $stock_transfer)
    {
        return view('focus.stock_transfers.edit', compact('stock_transfer', 'leave_categories', 'users'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  StockTransfer $stock_transfer
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, StockTransfer $stock_transfer)
    {
        $this->repository->update($stock_transfer, $request->except('_token'));

        return new RedirectResponse(route('biller.stock_transfers.index'), ['flash_success' => 'StockTransfer Updated Successfully']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  StockTransfer $stock_transfer
     * @return \Illuminate\Http\Response
     */
    public function destroy(StockTransfer $stock_transfer)
    {
        $this->repository->delete($stock_transfer);

        return new RedirectResponse(route('biller.stock_transfers.index'), ['flash_success' => 'StockTransfer Deleted Successfully']);
    }


    /**
     * Display the specified resource.
     *
     * @param  StockTransfer $stock_transfer
     * @return \Illuminate\Http\Response
     */
    public function show(StockTransfer $stock_transfer)
    {
        return view('focus.stock_transfers.view', compact('stock_transfer'));
    }
}
