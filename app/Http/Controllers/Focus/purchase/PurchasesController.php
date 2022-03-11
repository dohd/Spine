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

use App\Models\hrm\Hrm;

use App\Http\Requests\Focus\purchase\StorePurchaseRequest;

/**
 * PurchaseordersController
 */
class PurchasesController extends Controller
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
        $input = $request->only('rel_type', 'rel_id');
        $segment = false;
        $words = array();

        if (isset($input['rel_id']) and isset($input['rel_type'])) {
            switch ($input['rel_type']) {
                case 1:
                    $segment = Supplier::find($input['rel_id']);
                    $words['name'] = trans('customers.title');
                    $words['name_data'] = $segment->name;
                    break;
                case 2:
                    $segment = Hrm::find($input['rel_id']);
                    $words['name'] = trans('hrms.employee');
                    $words['name_data'] = $segment->first_name . ' ' . $segment->last_name;
                    break;
            }
        }

        return new ViewResponse('focus.purchases.index', compact('input', 'segment', 'words'));
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
        $bill = $request->only([
            'supplier_type', 'supplier_id', 'supplier', 'supplier_taxid', 'transxn_ref', 'date', 'due_date', 'doc_ref_type', 'doc_ref', 
            'project_id', 'note', 'stock_subttl', 'stock_tax', 'stock_grandttl', 'expense_subttl', 'expense_tax', 'expense_grandttl',
            'asset_tax', 'asset_subttl', 'asset_grandttl', 'grandtax', 'grandttl', 'paidttl', 'payment_status'
        ]);
        $bill_items = $request->only([
            'item_id', 'description', 'itemproject_id', 'qty', 'rate', 'tax_rate', 'tax', 'amount', 'type'
        ]);

        $bill['ins'] = auth()->user()->ins;
        $bill['user_id'] = auth()->user()->id;
        // modify and filter items without item_id
        $bill_items = modify_array($bill_items);
        $bill_items = array_filter($bill_items, function ($val) { return $val['item_id']; });

        $result = $this->repository->create(compact('bill', 'bill_items'));

        return response()->json(['status' => 'Success', 'message' => 'Direct purchase successful']);
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
        // 
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param DeletePurchaseorderRequestNamespace $request
     * @param App\Models\purchaseorder\Purchaseorder $purchaseorder
     * @return \App\Http\Responses\RedirectResponse
     */
    public function destroy(Purchase $purchase, StorePurchaseRequest $request)
    {
        // 
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param DeletePurchaseorderRequestNamespace $request
     * @param App\Models\purchaseorder\Purchaseorder $purchaseorder
     * @return \App\Http\Responses\RedirectResponse
     */
    public function show(Purchase $purchase, ManagePurchaseRequest $request)
    {
        // 
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
