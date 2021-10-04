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
namespace App\Http\Controllers\Focus\makepayment;

use App\Models\makepayment\Makepayment;
use App\Models\purchase\Purchase;

use App\Models\supplier\Supplier;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Responses\RedirectResponse;
use App\Http\Responses\ViewResponse;
use App\Http\Responses\Focus\makepayment\CreateResponse;
use App\Http\Responses\Focus\makepayment\EditResponse;
use App\Repositories\Focus\makepayment\MakepaymentRepository;
use App\Http\Requests\Focus\makepayment\ManageMakepaymentRequest;
use App\Models\transactioncategory\Transactioncategory;


//Ported
use App\Models\account\Account;
use App\Models\Company\ConfigMeta;
use App\Models\customer\Customer;
use App\Models\hrm\Hrm;
use mPDF;

use App\Http\Requests\Focus\makepayment\CreateMakepaymentRequest;
use App\Http\Requests\Focus\makepayment\StoreMakepaymentRequest;
//use App\Http\Requests\Focus\purchase\EditPurchaseorderRequest;
//use App\Http\Requests\Focus\purchase\UpdatePurchaseorderRequest;
//use App\Http\Requests\Focus\purchase\DeletePurchaseorderRequest;

/**
 * PurchaseordersController
 */
class MakepaymentsController extends Controller
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
    public function __construct(MakepaymentRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Display a listing of the resource.
     *
     * @param App\Http\Requests\Focus\purchaseorder\ManagePurchaseorderRequest $request
     * @return \App\Http\Responses\ViewResponse
     */

     public function single_payment(Makepayment $makepayment, StoreMakepaymentRequest $request, $tr_id)
    {


       $last_id=Purchase::orderBy('id', 'desc')->first();
       $transactions=Makepayment::where('id',$tr_id)->first();
       $accounts=Account::where('account_type', 'Assets')->get();
        return new CreateResponse('focus.makepayment.single_payment', compact('last_id', 'transactions', 'accounts'));
    }
    

public function receive_single_payment (Makepayment $makepayment, StoreMakepaymentRequest $request, $tr_id)
    {


       $last_id=Purchase::orderBy('id', 'desc')->first();
       $transactions=Makepayment::where('id',$tr_id)->first();
       $accounts=Account::where('account_type', 'Assets')->get();
        return new CreateResponse('focus.receivepayments.single_payment', compact('last_id', 'transactions', 'accounts'));
    }

    public function index(ManagePurchaseRequest $request)
    {
        $input = $request->only('rel_type', 'rel_id');
        $segment = false;
        $words = array();
        if (isset($input['rel_id']) and isset($input['rel_type'])) {
            switch ($input['rel_type']) {
                case 1 :
                    $segment = Supplier::find($input['rel_id']);
                    $words['name'] = trans('customers.title');
                    $words['name_data'] = $segment->name;
                    break;
                case 2 :
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




    public function store(StoreMakepaymentRequest $request)
    {

       
     $invoice = $request->only(['id', 'tid', 'method', 'refer_no', 'note', 'account_id']);
      $debit_entry = $request->only(['id', 'payer_id', 'tid', 'method', 'refer_no', 'note']);
      $invoice['ins'] = auth()->user()->ins;
      $invoice['user_id'] = auth()->user()->id;
      $invoice['credit'] = numberClean($request->input('amount_paid'));
      $invoice['for_who'] = numberClean($request->input('payer_id'));
      $invoice['transaction_date'] = date_for_database($request->input('transaction_date'));

      $debit_entry['ins'] = auth()->user()->ins;
      $debit_entry['user_id'] = auth()->user()->id;
      $debit_entry['debit'] = numberClean($request->input('amount_paid'));
      $debit_entry['for_who'] = $request->input('payer_id');
      $debit_entry['transaction_date'] = date_for_database($request->input('transaction_date'));

      $result = $this->repository->create(compact('invoice','debit_entry'));
     
  echo json_encode(array('status' => 'Success', 'message' => trans('alerts.backend.purchaseorders.created') . ' <a href="' . route('biller.purchases.show', [$result->id]) . '" class="btn btn-primary btn-md"><span class="fa fa-eye" aria-hidden="true"></span> ' . trans('general.view') . '  </a><a href="' . route('biller.purchases.create').'" class="btn btn-outline-light round btn-min-width bg-purple"><span class="fa fa-plus-circle" aria-hidden="true"></span>Add Another Transaction  </a> &nbsp; &nbsp;'));

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
       // return new EditResponse($purchaseorder);
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
        if($q=='supplier'){
           $result =  \App\Models\supplier\Supplier::select('id','suppliers.company AS name')->get();
       
             //$result = Branch::all()->where('rel_id', '=', $q);

        }
       
        return json_encode($result);
    }



}
