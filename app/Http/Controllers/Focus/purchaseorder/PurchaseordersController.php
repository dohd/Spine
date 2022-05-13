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

namespace App\Http\Controllers\Focus\purchaseorder;

use App\Models\purchaseorder\Purchaseorder;
use App\Http\Controllers\Controller;
use App\Http\Responses\ViewResponse;
use App\Http\Responses\Focus\purchaseorder\EditResponse;
use App\Repositories\Focus\purchaseorder\PurchaseorderRepository;

use App\Http\Requests\Focus\purchaseorder\StorePurchaseorderRequest;
use App\Http\Responses\Focus\purchaseorder\CreateResponse;
use App\Http\Responses\RedirectResponse;
use App\Models\items\GrnItem;

/**
 * PurchaseordersController
 */
class PurchaseordersController extends Controller
{
    /**
     * variable to store the repository object
     * @var PurchaseorderRepository
     */
    protected $repository;

    /**
     * contructor to initialize repository object
     * @param PurchaseorderRepository $repository ;
     */
    public function __construct(PurchaseorderRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Display a listing of the resource.
     *
     * @param App\Http\Requests\Focus\purchaseorder\ManagePurchaseorderRequest $request
     * @return \App\Http\Responses\ViewResponse
     */
    public function index()
    {
        return new ViewResponse('focus.purchaseorders.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @param CreatePurchaseorderRequestNamespace $request
     * @return \App\Http\Responses\Focus\purchaseorder\CreateResponse
     */
    public function create(StorePurchaseorderRequest $request)
    {
        return new CreateResponse('focus.purchaseorders.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param StoreInvoiceRequestNamespace $request
     * @return \App\Http\Responses\RedirectResponse
     */
    public function store(StorePurchaseorderRequest $request)
    {
        // extract input fields
        $order = $request->only([
            'supplier_id', 'tid', 'date', 'due_date', 'doc_ref_type', 'doc_ref', 'project_id', 'note', 'tax',
            'stock_subttl', 'stock_tax', 'stock_grandttl', 'expense_subttl', 'expense_tax', 'expense_grandttl',
            'asset_tax', 'asset_subttl', 'asset_grandttl', 'grandtax', 'grandttl', 'paidttl'
        ]);
        $order_items = $request->only([
            'item_id', 'description', 'uom', 'itemproject_id', 'qty', 'rate', 'taxrate', 'itemtax', 'amount', 'type'
        ]);

        $order['ins'] = auth()->user()->ins;
        $order['user_id'] = auth()->user()->id;
        // modify and filter items without item_id
        $order_items = modify_array($order_items);
        $order_items = array_filter($order_items, function ($v) { return $v['item_id']; });

        $result = $this->repository->create(compact('order', 'order_items'));

        return new RedirectResponse(route('biller.purchaseorders.index'), ['flash_success' => 'Purchase Order created successfully']);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param App\Models\purchaseorder\Purchaseorder $purchaseorder
     * @param EditPurchaseorderRequestNamespace $request
     * @return \App\Http\Responses\Focus\purchaseorder\EditResponse
     */
    public function edit(Purchaseorder $purchaseorder)
    {
        return new EditResponse($purchaseorder);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param UpdatePurchaseorderRequestNamespace $request
     * @param App\Models\purchaseorder\Purchaseorder $purchaseorder
     * @return \App\Http\Responses\RedirectResponse
     */
    public function update(StorePurchaseorderRequest $request, Purchaseorder $purchaseorder)
    {
        // extract input fields
        $order = $request->only([
            'supplier_id', 'tid', 'date', 'due_date', 'doc_ref_type', 'doc_ref', 'project_id', 'note', 'tax',
            'stock_subttl', 'stock_tax', 'stock_grandttl', 'expense_subttl', 'expense_tax', 'expense_grandttl',
            'asset_tax', 'asset_subttl', 'asset_grandttl', 'grandtax', 'grandttl', 'paidttl'
        ]);
        $order_items = $request->only([
            'id', 'item_id', 'description', 'uom', 'itemproject_id', 'qty', 'rate', 'taxrate', 'itemtax', 'amount', 'type'
        ]);

        $order['ins'] = auth()->user()->ins;
        $order['user_id'] = auth()->user()->id;
        // modify and filter items without item_id
        $order_items = modify_array($order_items);
        $order_items = array_filter($order_items, function ($val) { return $val['item_id']; });

        $result = $this->repository->update($purchaseorder, compact('order', 'order_items'));

        return new RedirectResponse(route('biller.purchaseorders.index'), ['flash_success' => trans('alerts.backend.purchaseorders.updated')]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param DeletePurchaseorderRequestNamespace $request
     * @param App\Models\purchaseorder\Purchaseorder $purchaseorder
     * @return \App\Http\Responses\RedirectResponse
     */
    public function destroy(Purchaseorder $purchaseorder)
    {
        $this->repository->delete($purchaseorder);
        
        return response()->json(['status' => 'Success', 'message' => trans('alerts.backend.purchaseorders.deleted')]);
    }


    /**
     * Remove the specified resource from storage.
     *
     * @param DeletePurchaseorderRequestNamespace $request
     * @param App\Models\purchaseorder\Purchaseorder $purchaseorder
     * @return \App\Http\Responses\RedirectResponse
     */
    public function show(Purchaseorder $purchaseorder)
    {   
        $grn_items = GrnItem::whereHas('grn', function ($q) use($purchaseorder) {
            $q->where('purchaseorder_id', $purchaseorder->id);
        })->get(); 

        return new ViewResponse('focus.purchaseorders.view', compact('purchaseorder', 'grn_items'));
    }

    /**
     * 
     */
    public function create_grn(StorePurchaseorderRequest $request, Purchaseorder $purchaseorder)
    {
        return new ViewResponse('focus.purchaseorders.create_grn', compact('purchaseorder'));
    }

    /**
     * Receive purchase order goods
     */
    public function store_grn(StorePurchaseorderRequest $request, Purchaseorder $purchaseorder)
    {
        // extract input fields
        $order = $request->only([
            'stock_grn', 'expense_grn', 'asset_grn',
            'stock_subttl', 'stock_tax', 'stock_grandttl', 'expense_subttl', 'expense_tax', 'expense_grandttl',
            'asset_tax', 'asset_subttl', 'asset_grandttl', 'grandtax', 'grandttl', 'paidttl'
        ]);
        $order_items = $request->only(['poitem_id', 'qty', 'dnote', 'date']);

        $order['purchaseorder_id'] = $purchaseorder->id;
        $order['ins'] = auth()->user()->ins;
        $order['user_id'] = auth()->user()->id;
        // modify and filter items with 0 qty
        $order_items = modify_array($order_items);
        $order_items = array_filter($order_items, function ($item) { return $item['qty']; });

        $result = $this->repository->create_grn($purchaseorder, compact('order', 'order_items'));

        return new RedirectResponse(route('biller.purchaseorders.index'), ['flash_success' => 'Purchase Order Goods successfully received']);
    }
}
