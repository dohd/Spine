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

namespace App\Http\Controllers\Focus\purchase;

use App\Models\purchase\Purchase;
use App\Models\supplier\Supplier;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Responses\ViewResponse;
use App\Http\Responses\Focus\purchase\CreateResponse;
use App\Http\Responses\Focus\purchase\EditResponse;
use App\Repositories\Focus\purchase\PurchaseRepository;
use App\Http\Requests\Focus\purchase\ManagePurchaseRequest;


use App\Http\Requests\Focus\purchase\StorePurchaseRequest;
use App\Http\Responses\RedirectResponse;
use Illuminate\Validation\ValidationException;
use Redirect;

/**
 * PurchaseordersController
 */
class PurchasesController extends Controller
{
    /**
     * variable to store the repository object
     * @var PurchaseRepository
     */
    protected $repository;

    /**
     * contructor to initialize repository object
     * @param PurchaseRepository $repository ;
     */
    public function __construct(PurchaseRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Display a listing of the resource.
     *
     * @param App\Http\Requests\Focus\purchaseorder\ManagePurchaseorderRequest $request
     * @return \App\Http\Responses\ViewResponse
     */
    public function index(ManagePurchaseRequest $request)
    {
        return new ViewResponse('focus.purchases.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @param CreatePurchaseorderRequestNamespace $request
     * @return \App\Http\Responses\Focus\purchaseorder\CreateResponse
     */
    public function create(StorePurchaseRequest $request)
    {
        return new CreateResponse('focus.purchases.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param StoreInvoiceRequestNamespace $request
     * @return \App\Http\Responses\RedirectResponse
     */
    public function store(StorePurchaseRequest $request)
    {
        // extract input details
        $data = $request->only([
            'supplier_type', 'supplier_id', 'suppliername', 'supplier_taxid', 'transxn_ref', 'date', 'due_date', 'doc_ref_type', 'doc_ref', 
            'tax', 'tid', 'project_id', 'note', 'stock_subttl', 'stock_tax', 'stock_grandttl', 'expense_subttl', 'expense_tax', 'expense_grandttl',
            'asset_tax', 'asset_subttl', 'asset_grandttl', 'grandtax', 'grandttl', 'paidttl', 'is_tax_exc'
        ]);
        $data_items = $request->only([
            'item_id', 'description', 'itemproject_id', 'qty', 'rate', 'taxrate', 'itemtax', 'amount', 'type'
        ]);

        $data['ins'] = auth()->user()->ins;
        $data['user_id'] = auth()->user()->id;

        $data_items = modify_array($data_items);
        // filter non auto-generated items
        $data_items = array_filter($data_items, function ($v) { return $v['item_id']; });
        if (!$data_items) throw ValidationException::withMessages(['Please use auto-generated items as line items!']);

        $this->repository->create(compact('data', 'data_items'));

        return new RedirectResponse(route('biller.purchases.index'), ['flash_success' => 'Direct Purchase posted successfully']);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param App\Models\purchaseorder\Purchaseorder $purchaseorder
     * @param EditPurchaseorderRequestNamespace $request
     * @return \App\Http\Responses\Focus\purchaseorder\EditResponse
     */
    public function edit(Purchase $purchase, StorePurchaseRequest $request)
    {
        return new EditResponse($purchase);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param UpdatePurchaseorderRequestNamespace $request
     * @param App\Models\purchaseorder\Purchaseorder $purchaseorder
     * @return \App\Http\Responses\RedirectResponse
     */
    public function update(StorePurchaseRequest $request, Purchase $purchase)
    {
        // extract input details
        $data = $request->only([
            'supplier_type', 'supplier_id', 'suppliername', 'supplier_taxid', 'transxn_ref', 'date', 'due_date', 'doc_ref_type', 'doc_ref', 
            'tax', 'project_id', 'note', 'stock_subttl', 'stock_tax', 'stock_grandttl', 'expense_subttl', 'expense_tax', 'expense_grandttl',
            'asset_tax', 'asset_subttl', 'asset_grandttl', 'grandtax', 'grandttl', 'paidttl', 'is_tax_exc'
        ]);
        $data_items = $request->only([
            'id', 'item_id', 'description', 'itemproject_id', 'qty', 'rate', 'taxrate', 'itemtax', 'amount', 'type'
        ]);

        $data['ins'] = auth()->user()->ins;
        $data['user_id'] = auth()->user()->id;

        $data_items = modify_array($data_items);
        // filter non auto-generated items
        $data_items = array_filter($data_items, function ($v) { return $v['item_id']; });
        if (!$data_items) throw ValidationException::withMessages(['Please use auto-generated items as line items!']);

        $this->repository->update($purchase, compact('data', 'data_items'));

        return new RedirectResponse(route('biller.purchases.index'), ['flash_success' => 'Direct Purchase updated successfully']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param DeletePurchaseorderRequestNamespace $request
     * @param App\Models\purchaseorder\Purchaseorder $purchaseorder
     * @return \App\Http\Responses\RedirectResponse
     */
    public function destroy(Purchase $purchase)
    {
        $this->repository->delete($purchase);
        
        return response()->json(['status' => 'Success', 'message' => 'Purchase record deleted successfully']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param DeletePurchaseorderRequestNamespace $request
     * @param App\Models\purchaseorder\Purchaseorder $purchaseorder
     * @return \App\Http\Responses\RedirectResponse
     */
    public function show(Purchase $purchase)
    {
        return new ViewResponse('focus.purchases.view', compact('purchase'));
    }

    public function customer_load(Request $request)
    {
        $q = $request->get('id');

        $suppliers = array();
        if ($q == 'supplier') 
            $suppliers = Supplier::select('id', 'suppliers.company AS name')->get();

        return response()->json($suppliers);
    }
}
