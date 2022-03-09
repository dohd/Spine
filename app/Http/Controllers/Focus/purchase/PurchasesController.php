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
            'asset_tax', 'asset_subttl', 'asset_grandttl', 'grand_tax', 'grand_ttl', 'paid_ttl'
        ]);
        $bill_items = $request->only([
            'item_id', 'description', 'itemproject_id', 'qty', 'rate', 'tax_rate', 'tax', 'amount', 'type'
        ]);


        dd($bill);

        print_log('+++ Direct Purchase ++++');
        return response()->json(['status' => 'Success', 'message' => 'Direct purchase successful']);






        $invoice = $request->only(['payer_type', 'payer', 'payer_id', 'tid', 'ref_type', 'taxformat', 'discountformat', 's_warehouses']);

        $tax = $request->only(['payer_type', 'payer', 'payer_id', 'taxid', 'tid', 'ref_type', 'taxformat']);


        $inventory_items = $request->only(['product_id', 'product_name', 'product_qty', 'product_price', 'product_tax', 'product_discount', 'product_subtotal', 'total_tax', 'total_discount', 'product_description', 'u_m', 'taxedvalue', 'salevalue', 'client_id', 'branch_id', 'inventory_project_id']);

        $expense_items = $request->only(['ledger_id', 'exp_product_qty', 'exp_product_price', 'exp_product_tax', 'exp_product_discount', 'exp_product_subtotal', 'total_tax', 'exp_total_discount', 'exp_product_description', 'exp_taxedvalue', 'exp_salevalue', 'exp_client_id', 'exp_branch_id', 'exp_project_id']);

        $stockable_items = $request->only(['item_id', 'account_id', 'account_type', 'itemname', 'item_product_qty', 'item_product_price', 'item_product_tax', 'item_product_discount', 'item_total_tax', 'item_product_subtotal', 'item_total_tax', 'item_total_discount', 'item_product_description', 'item_taxedvalue', 'item_salevalue', 'item_taxedvalue', 'item_client_id', 'item_branch_id', 'item_project_id']);


        if (numberClean($request->input('grandtaxs')) > 0 && empty($request->input('taxid'))) {
            echo json_encode(array('status' => 'Error', 'message' => 'Tax Pin Must be Provided'));
            exit;
        }

        //invoices
        if ($request->input('ref_type') == "Invoice") {
            $refer_no = "INV-" . $request->input('refer_no');
        } else if ($request->input('ref_type') == "Receipt") {
            $refer_no = "RCPT-" . $request->input('refer_no');
        } else if ($request->input('ref_type') == "DNote") {
            $refer_no = "DN-" . $request->input('refer_no');
        } else if ($request->input('ref_type') == "Voucher") {
            $refer_no = "VOU-" . $request->input('refer_no');
        }

        $invoice['ins'] = auth()->user()->ins;
        $invoice['user_id'] = auth()->user()->id;
        $invoice['refer_no'] = $refer_no;
        $invoice['account_id'] = $request->input('credit_account_id');
        $invoice['project_id'] = $request->input('all_project_id');
        $invoice['for_who'] = $request->input('payer_id');

        $invoice['transaction_date'] = date_for_database($request->input('transaction_date'));
        $invoice['due_date'] = date_for_database($request->input('due_date'));
        $invoice['credit'] = numberClean($request->input('finaltotals'));
        $invoice['discount'] = numberClean($request->input('granddiscounts'));
        $invoice['tax_amount'] = numberClean($request->input('grandtaxs'));
        $invoice['taxable_amount'] = numberClean($request->input('grandtaxable'));
        $invoice['total_amount'] = numberClean($request->input('finaltotals'));
        $invoice['note'] = strip_tags($request->input('note'));



        //tax
        $tax['ins'] = auth()->user()->ins;
        $tax['user_id'] = auth()->user()->id;
        $tax['refer_no'] = $refer_no;
        $tax['for_who'] = $request->input('payer_id');
        $tax['tax_amount'] = numberClean($request->input('grandtaxs'));
        $tax['debit'] = numberClean($request->input('grandtaxs'));
        $tax['taxable_amount'] = numberClean($request->input('grandtaxable'));
        $tax['note'] = strip_tags($request->input('note'));
        $tax['transaction_date'] = date_for_database($request->input('transaction_date'));


        //inventory tab
        $inventory_items['user_id'] = auth()->user()->id;
        $inventory_items['ins'] = auth()->user()->ins;
        $inventory_items['tid'] = $request->input('tid');
        $inventory_items['for_who'] = $request->input('payer_id');
        $inventory_items['transaction_date'] = date_for_database($request->input('transaction_date'));
        $inventory_items['s_warehouses'] = numberClean($request->input('s_warehouses'));
        $inventory_items['totalsaleamount'] = numberClean($request->input('totalsaleamount'));


        //stockable Inventory
        $stockable_items['user_id'] = auth()->user()->id;
        $stockable_items['ins'] = auth()->user()->ins;
        $stockable_items['tid'] = $request->input('tid');
        $stockable_items['for_who'] = $request->input('payer_id');
        $stockable_items['transaction_date'] = date_for_database($request->input('transaction_date'));
        $stockable_items['s_warehouses'] = numberClean($request->input('s_warehouses'));
        $stockable_items['item_totalsaleamount'] = numberClean($request->input('item_totalsaleamount'));



        //expense tab
        $expense_items['user_id'] = auth()->user()->id;
        $expense_items['ins'] = auth()->user()->ins;
        $expense_items['tid'] = $request->input('tid');
        $expense_items['for_who'] = $request->input('payer_id');
        $expense_items['transaction_date'] = date_for_database($request->input('transaction_date'));
        $expense_items['exp_totalsaleamount'] = numberClean($request->input('exp_totalsaleamount'));

        //$invoice_items['ins'] = auth()->user()->ins;

        $data2['ins'] = auth()->user()->ins;

        $result = $this->repository->create(compact('invoice', 'inventory_items', 'tax', 'expense_items', 'stockable_items', 'data2'));



        echo json_encode(array('status' => 'Success', 'message' => trans('alerts.backend.purchaseorders.created') . ' <a href="' . route('biller.purchases.show', [$result->id]) . '" class="btn btn-primary btn-md"><span class="fa fa-eye" aria-hidden="true"></span> ' . trans('general.view') . '  </a> <a href="' . route('biller.makepayment.single_payment', [$result->id]) . '" class="btn btn-outline-light round btn-min-width bg-purple"><span class="fa fa-plus-circle" aria-hidden="true"></span>Make Payment  </a>&nbsp; &nbsp;'));
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


        $invoice = $request->only(['id', 'payer_type', 'payer', 'payer_id', 'tid', 'ref_type', 'taxformat', 'discountformat', 's_warehouses']);

        $tax = $request->only(['payer_type', 'payer', 'payer_id', 'taxid', 'tid', 'ref_type', 'taxformat']);


        $inventory_items = $request->only(['product_id', 'product_name', 'product_qty', 'product_price', 'product_tax', 'product_discount', 'product_subtotal', 'total_tax', 'total_discount', 'product_description', 'u_m', 'taxedvalue', 'salevalue', 'client_id', 'branch_id', 'inventory_project_id']);

        $expense_items = $request->only(['ledger_id', 'exp_product_qty', 'exp_product_price', 'exp_product_tax', 'exp_product_discount', 'exp_product_subtotal', 'total_tax', 'exp_total_discount', 'exp_product_description', 'exp_taxedvalue', 'exp_salevalue', 'exp_client_id', 'exp_branch_id', 'exp_project_id']);

        $stockable_items = $request->only(['account_id', 'itemname', 'item_product_qty', 'item_product_price', 'item_product_tax', 'item_product_discount', 'item_total_tax', 'item_product_subtotal', 'item_total_tax', 'item_total_discount', 'item_product_description', 'item_taxedvalue', 'item_salevalue', 'item_taxedvalue', 'item_client_id', 'item_branch_id', 'item_project_id']);


        if (numberClean($request->input('grandtaxs')) > 0 && empty($request->input('taxid'))) {
            echo json_encode(array('status' => 'Error', 'message' => 'Tax Pin Must be Provided'));
            exit;
        }

        //invoices
        if ($request->input('ref_type') == "Invoice") {
            $refer_no = "INV-" . $request->input('refer_no');
        } else if ($request->input('ref_type') == "Receipt") {
            $refer_no = "RCPT-" . $request->input('refer_no');
        } else if ($request->input('ref_type') == "DNote") {
            $refer_no = "DN-" . $request->input('refer_no');
        } else if ($request->input('ref_type') == "Voucher") {
            $refer_no = "VOU-" . $request->input('refer_no');
        }

        $invoice['ins'] = auth()->user()->ins;
        $invoice['user_id'] = auth()->user()->id;
        $invoice['refer_no'] = $refer_no;
        $invoice['account_id'] = $request->input('credit_account_id');
        $invoice['project_id'] = $request->input('all_project_id');
        $invoice['transaction_date'] = date_for_database($request->input('transaction_date'));
        $invoice['due_date'] = date_for_database($request->input('due_date'));
        $invoice['credit'] = numberClean($request->input('finaltotals'));
        $invoice['discount'] = numberClean($request->input('granddiscounts'));
        $invoice['tax_amount'] = numberClean($request->input('grandtaxs'));
        $invoice['taxable_amount'] = numberClean($request->input('grandtaxable'));
        $invoice['total_amount'] = numberClean($request->input('finaltotals'));
        $invoice['note'] = strip_tags($request->input('note'));



        //tax
        $tax['ins'] = auth()->user()->ins;
        $tax['user_id'] = auth()->user()->id;
        $tax['refer_no'] = $refer_no;
        $tax['tax_amount'] = numberClean($request->input('grandtaxs'));
        $tax['debit'] = numberClean($request->input('grandtaxs'));
        $tax['taxable_amount'] = numberClean($request->input('grandtaxable'));
        $tax['note'] = strip_tags($request->input('note'));
        $tax['transaction_date'] = date_for_database($request->input('transaction_date'));


        //inventory tab
        $inventory_items['user_id'] = auth()->user()->id;
        $inventory_items['ins'] = auth()->user()->ins;
        $inventory_items['tid'] = $request->input('tid');
        $inventory_items['transaction_date'] = date_for_database($request->input('transaction_date'));
        $inventory_items['s_warehouses'] = numberClean($request->input('s_warehouses'));
        $inventory_items['totalsaleamount'] = numberClean($request->input('totalsaleamount'));

        //expense tab
        $expense_items['user_id'] = auth()->user()->id;
        $expense_items['ins'] = auth()->user()->ins;
        $expense_items['tid'] = $request->input('tid');
        $expense_items['transaction_date'] = date_for_database($request->input('transaction_date'));
        $expense_items['exp_totalsaleamount'] = numberClean($request->input('exp_totalsaleamount'));



        //$invoice_items['ins'] = auth()->user()->ins;

        $data2['ins'] = auth()->user()->ins;

        $result = $this->repository->update($purchase, compact('invoice', 'inventory_items', 'tax', 'expense_items', 'stockable_items', 'data2'));



        echo json_encode(array('status' => 'Success', 'message' => trans('alerts.backend.purchaseorders.created') . ' <a href="' . route('biller.purchases.show', [$result->id]) . '" class="btn btn-primary btn-md"><span class="fa fa-eye" aria-hidden="true"></span> ' . trans('general.view') . '  </a> <a href="' . route('biller.makepayment.single_payment', [$result->id]) . '" class="btn btn-outline-light round btn-min-width bg-purple"><span class="fa fa-plus-circle" aria-hidden="true"></span>Make Payment  </a>&nbsp; &nbsp;'));




        /* 
        $invoice = $request->only(['supplier_id', 'id', 'refer', 'invoicedate', 'invoiceduedate', 'notes', 'subtotal', 'shipping', 'tax', 'discount', 'discount_rate', 'after_disc', 'currency', 'total', 'tax_format', 'discount_format', 'ship_tax', 'ship_tax_type', 'ship_rate', 'ship_tax', 'term_id', 'tax_id', 'restock']);
        $invoice_items = $request->only(['product_id', 'product_name', 'code', 'product_qty', 'product_price', 'product_tax', 'product_discount', 'product_subtotal', 'product_subtotal', 'total_tax', 'total_discount', 'product_description', 'unit', 'old_product_qty']);
    
        $invoice['ins'] = auth()->user()->ins;
      
        $invoice_items['ins'] = auth()->user()->ins;
   
        $data2 = $request->only(['custom_field']);
        $data2['ins'] = auth()->user()->ins;


        $result = $this->repository->update($purchaseorder, compact('invoice', 'invoice_items', 'data2'));

     

        echo json_encode(array('status' => 'Success', 'message' => trans('alerts.backend.purchaseorders.updated') . ' <a href="' . route('biller.purchaseorders.show', [$result->id]) . '" class="btn btn-primary btn-md"><span class="fa fa-eye" aria-hidden="true"></span> ' . trans('general.view') . '  </a> &nbsp; &nbsp;'));*/
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
        /* $this->repository->delete($purchaseorder);
        
        return json_encode(array('status' => 'Success', 'message' => trans('alerts.backend.purchaseorders.deleted')));*/
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
        /*
        $accounts = Account::all();
       
        $purchaseorder['bill_type'] = 1;
        $words['prefix'] = prefix(9);
        $words['pay_note'] = trans('purchaseorders.payment_for_order') . ' ' . $words['prefix'] . '#' . $purchaseorder->tid;

        return new ViewResponse('focus.purchaseorders.view', compact('purchaseorder', 'accounts', 'features', 'words'));*/
    }

    public function customer_load(Request $request)
    {
        $q = $request->get('id');
        if ($q == 'supplier') {
            $result =  \App\Models\supplier\Supplier::select('id', 'suppliers.company AS name')->get();
            //$result = Branch::all()->where('rel_id', '=', $q);
        }

        return json_encode($result);
    }
}
