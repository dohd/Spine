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
use App\Models\supplier\Supplier;
use App\Http\Controllers\Controller;
use App\Http\Responses\ViewResponse;
use App\Http\Responses\Focus\purchaseorder\EditResponse;
use App\Repositories\Focus\purchaseorder\PurchaseorderRepository;
use App\Http\Requests\Focus\purchaseorder\ManagePurchaseorderRequest;

//Ported
use App\Models\account\Account;
use App\Models\Company\ConfigMeta;
use App\Models\hrm\Hrm;

use App\Http\Requests\Focus\purchaseorder\StorePurchaseorderRequest;
use App\Http\Responses\Focus\purchaseorder\CreateResponse;
use App\Http\Responses\RedirectResponse;

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
    public function index(ManagePurchaseorderRequest $request)
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


        return new ViewResponse('focus.purchaseorders.index', compact('input', 'segment', 'words'));
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
        $bill = $request->only([
            'supplier_type', 'supplier_id', 'suppliername', 'supplier_taxid', 'transxn_ref', 'date', 'due_date', 'doc_ref_type', 'doc_ref', 
            'project_id', 'note', 'stock_subttl', 'stock_tax', 'stock_grandttl', 'expense_subttl', 'expense_tax', 'expense_grandttl',
            'asset_tax', 'asset_subttl', 'asset_grandttl', 'grandtax', 'grandttl', 'paidttl'
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

        return new RedirectResponse(route('biller.purchaseorders.index'), ['flash_success' => 'Purchase Order created successfully']);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param App\Models\purchaseorder\Purchaseorder $purchaseorder
     * @param EditPurchaseorderRequestNamespace $request
     * @return \App\Http\Responses\Focus\purchaseorder\EditResponse
     */
    public function edit(Purchaseorder $purchaseorder, StorePurchaseorderRequest $request)
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

        //Input received from the request
        $invoice = $request->only(['supplier_id', 'id', 'refer', 'invoicedate', 'invoiceduedate', 'notes', 'subtotal', 'shipping', 'tax', 'discount', 'discount_rate', 'after_disc', 'currency', 'total', 'tax_format', 'discount_format', 'ship_tax', 'ship_tax_type', 'ship_rate', 'ship_tax', 'term_id', 'tax_id', 'restock']);
        $invoice_items = $request->only(['product_id', 'product_name', 'code', 'product_qty', 'product_price', 'product_tax', 'product_discount', 'product_subtotal', 'product_subtotal', 'total_tax', 'total_discount', 'product_description', 'unit', 'old_product_qty']);
        //dd($request->id);
        $invoice['ins'] = auth()->user()->ins;
        //$invoice['user_id']=auth()->user()->id;
        $invoice_items['ins'] = auth()->user()->ins;
        //Create the model using repository create method
        $data2 = $request->only(['custom_field']);
        $data2['ins'] = auth()->user()->ins;


        $result = $this->repository->update($purchaseorder, compact('invoice', 'invoice_items', 'data2'));

        //return with successfull message

        echo json_encode(array('status' => 'Success', 'message' => trans('alerts.backend.purchaseorders.updated') . ' <a href="' . route('biller.purchaseorders.show', [$result->id]) . '" class="btn btn-primary btn-md"><span class="fa fa-eye" aria-hidden="true"></span> ' . trans('general.view') . '  </a> &nbsp; &nbsp;'));
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
        $po = $purchaseorder;
        
        return new ViewResponse('focus.purchaseorders.view', compact('po'));
    }
}
