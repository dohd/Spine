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

namespace App\Http\Controllers\Focus\quote;

use App\Http\Requests\Focus\invoice\ManageInvoiceRequest;
use App\Models\quote\Quote;
use App\Repositories\Focus\invoice\InvoiceRepository;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Responses\RedirectResponse;
use App\Http\Responses\ViewResponse;
use App\Http\Responses\Focus\quote\CreateResponse;
use App\Http\Responses\Focus\quote\EditResponse;
use App\Repositories\Focus\quote\QuoteRepository;
use App\Http\Requests\Focus\quote\ManageQuoteRequest;
use App\Models\account\Account;
use App\Models\Company\ConfigMeta;
use App\Models\customer\Customer;
use App\Models\hrm\Hrm;
use App\Http\Requests\Focus\quote\CreateQuoteRequest;
use App\Http\Requests\Focus\quote\EditQuoteRequest;
use App\Http\Requests\Focus\quote\DeleteQuoteRequest;
use App\Models\bank\Bank;
use App\Models\lead\Lead;

/**
 * QuotesController
 */
class QuotesController extends Controller
{
    /**
     * variable to store the repository object
     * @var QuoteRepository
     */
    protected $repository;

    /**
     * contructor to initialize repository object
     * @param QuoteRepository $repository ;
     */
    public function __construct(QuoteRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Display a listing of the resource.
     *
     * @param App\Http\Requests\Focus\quote\ManageQuoteRequest $request
     * @return \App\Http\Responses\ViewResponse
     */
    public function index(ManageQuoteRequest $request)
    {
        $input = $request->only('rel_type', 'rel_id');
        $segment = false;
        $words = array();
        if (isset($input['rel_id']) and isset($input['rel_type'])) {
            switch ($input['rel_type']) {
                case 1:
                    $segment = Customer::find($input['rel_id']);
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

        return new ViewResponse('focus.quotes.index', compact('input', 'segment', 'words'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @param CreateQuoteRequestNamespace $request
     * @return \App\Http\Responses\Focus\quote\CreateResponse
     */
    public function create(CreateQuoteRequest $request)
    {
        $page = $request->input('page');
        return new CreateResponse($page);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param StoreInvoiceRequestNamespace $request
     * @return \App\Http\Responses\RedirectResponse
     */
    public function store(CreateQuoteRequest $request)
    {
        // filter request input fields
        $data = $request->only(['tid', 'term_id', 'bank_id', 'invoicedate', 'notes', 'subtotal', 'extra_discount', 'currency', 'subtotal', 'tax', 'total', 'tax_format', 'term_id', 'tax_id', 'lead_id', 'attention', 'reference', 'reference_date', 'validity', 'pricing', 'prepaired_by', 'print_type']);
        $data_items = $request->only(['row_index', 'numbering', 'product_id', 'a_type', 'product_name', 'product_qty', 'product_price', 'product_subtotal', 'product_exclusive', 'total_tax', 'total_discount', 'unit']);

        $data['user_id'] = auth()->user()->id;
        $data['ins'] = auth()->user()->ins;

        $result = $this->repository->create(compact('data', 'data_items'));

        return new RedirectResponse(
            route('biller.quotes.index', [$result['id']]), 
            ['flash_success' => $this->flash_msg('Quote generated', $result['id'])]
        );
    }


    public function storeverified(ManageQuoteRequest $request)
    {
        //filter request input fields
        $invoice = $request->only(['quote_id', 'verified_amount', 'verified_disc', 'verified_tax', 'verified_amount']);
        $invoice_items = $request->only(['numbering', 'product_id', 'product_name', 'product_qty', 'product_price', 'product_subtotal', 'product_exclusive', 'total_tax', 'total_discount', 'unit']);
        $data2 = $request->only(['custom_field']);

        $data2['ins'] = auth()->user()->ins;
        $invoice['ins'] = auth()->user()->ins;
        $invoice['user_id'] = auth()->user()->id;
        $invoice_items['ins'] = auth()->user()->ins;

        $result = $this->repository->verify(compact('invoice', 'invoice_items', 'data2'));

        return new RedirectResponse(
            route('biller.quotes.index', [$result->id]), 
            ['flash_success' => $this->flash_msg('Quote verified', $result->id)]
        );
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param App\Models\quote\Quote $quote
     * @param EditQuoteRequestNamespace $request
     * @return \App\Http\Responses\Focus\quote\EditResponse
     */
    public function edit(Quote $quote)
    {
        return new EditResponse($quote);
    }

    // verify
    public function verify(Quote $quote, $qt_id)
    {
        $quote = Quote::find($qt_id);

        return view('focus.quotes.verify')->with(array('quote' => $quote))->with(bill_helper(2, 4));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param UpdateQuoteRequestNamespace $request
     * @param App\Models\quote\Quote $quote
     * @return \App\Http\Responses\RedirectResponse
     */
    public function update(EditQuoteRequest $request, Quote $quote)
    {
        //filter request input fields
        $data = $request->only(['tid', 'term_id', 'bank_id', 'invoicedate', 'notes', 'subtotal', 'extra_discount', 'currency', 'subtotal', 'tax', 'total', 'tax_format', 'revision', 'term_id', 'tax_id', 'lead_id', 'attention', 'reference', 'reference_date', 'validity', 'pricing', 'prepaired_by', 'print_type']);
        $data_items = $request->only(['row_index', 'item_id', 'numbering', 'product_id', 'product_name', 'product_qty', 'product_price', 'product_subtotal', 'unit']);

        $data['id'] = $quote->id;
        $data['ins'] = auth()->user()->ins;

        $result = $this->repository->update(compact('data', 'data_items'));

        return new RedirectResponse(
            route('biller.quotes.index', [$quote->id]), 
            ['flash_success' => $this->flash_msg('Quote updated', $quote->id)]
        );
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param DeleteQuoteRequestNamespace $request
     * @param App\Models\quote\Quote $quote
     * @return \App\Http\Responses\RedirectResponse
     */
    public function destroy(Quote $quote, DeleteQuoteRequest $request)
    {
        //Calling the delete method on repository
        $this->repository->delete($quote);
        //returning with successfull message
        return new RedirectResponse(route('biller.quotes.index'), ['flash_success' => trans('alerts.backend.quotes.deleted')]);
    }


    /**
     * Show the form for viewing the specified resource.
     *
     * @param DeleteQuoteRequestNamespace $request
     * @param App\Models\quote\Quote $quote
     * @return \App\Http\Responses\ViewResponse ViewResponse
     */
    public function show(Quote $quote, ManageQuoteRequest $request)
    {
        $accounts = Account::all();
        $features = ConfigMeta::where('feature_id', 9)->first();

        $quote['bill_type'] = 4;

        return new ViewResponse('focus.quotes.view', compact('quote', 'accounts', 'features'));
    }


    // default flash message format
    protected function flash_msg($message="", $id="")
    {
        return $message 
            . ' <a href="' . route('biller.quotes.index', [$id]) 
            . '" class="ml-5 btn btn-outline-light round btn-min-width bg-blue"><span class="fa fa-eye" aria-hidden="true"></span> ' 
            . trans('general.view') . '  </a> &nbsp; &nbsp;' . ' <a href="' . route('biller.quotes.create') 
            . '" class="btn btn-outline-light round btn-min-width bg-purple"><span class="fa fa-plus-circle" aria-hidden="true"></span> ' 
            . trans('general.create') . '  </a>&nbsp; &nbsp;' . ' <a href="' . route('biller.quotes.index') 
            . '" class="btn btn-outline-blue round btn-min-width bg-amber"><span class="fa fa-list blue" aria-hidden="true"></span> <span class="blue">' 
            . trans('general.list') . '</span> </a>';
    }

    /**
     * Show the form for Copying the resource in storage.
     *
     * @param App\Http\Requests\Focus\quote\EditQuoteRequest $request
     * @param App\Models\quote\Quote $quote
     * @return \App\Http\Responses\RedirectResponse
     */
    public function copy($id)
    {
        $quote = Quote::find($id);
        $leads = Lead::all();
        $last_quote = Quote::orderBy('id', 'desc')->where('i_class', '=', 0)->first();

        // edit proformer invoice
        if ($quote->bank_id ) {
            $banks = Bank::all();
            return view('focus.quotes.edit_pi')
                ->with(compact('quote', 'leads', 'last_quote', 'banks'))
                ->with(bill_helper(2, 4));
        }

        return view('focus.quotes.edit')
            ->with(compact('quote', 'leads', 'last_quote'))
            ->with(bill_helper(2, 4));
    }

    // Delete Quote product
    public function delete_product($id)
    {
        $this->repository->delete_product($id);
        return response()->noContent();
    }


    public function convert(InvoiceRepository $invoicerepository, ManageInvoiceRequest $request)
    {
        $input = $request->only(['bill_id', 'delete_item']);
        $quote_o = Quote::where('id', '=', $input['bill_id'])->first();
        //Input received from the request
        $invoice = array('customer_id' => $quote_o['customer_id'], 'tid' => $quote_o['tid'], 'refer' => $quote_o['refer'], 'invoicedate' => $quote_o['invoicedate'], 'invoiceduedate' => $quote_o['invoiceduedate'], 'notes' => $quote_o['notes'], 'subtotal' => $quote_o['subtotal'], 'shipping' => $quote_o['shipping'], 'tax' => $quote_o['tax'], 'discount' => $quote_o['discount'], 'discount_rate' => $quote_o['discount_rate'], 'after_disc' => $quote_o['after_disc'], 'total' => $quote_o['total'], 'tax_format' => $quote_o['tax_format'], 'discount_format' => $quote_o['discount_format'], 'ship_tax' => $quote_o['ship_tax'], 'ship_tax_type' => $quote_o['ship_tax_type'], 'ship_rate' => $quote_o['ship_rate'], 'term_id' => $quote_o['term_id'], 'tax_id' => $quote_o['tax_id']);
        $invoice_items = $quote_o->products;
        //$data2 = $request->only(['custom_field']);
        $data2['ins'] = auth()->user()->ins;
        //dd($invoice_items);
        $invoice['ins'] = auth()->user()->ins;
        $invoice['user_id'] = auth()->user()->id;

        //Create the model using repository create method
        $result = $invoicerepository->convert(compact('invoice', 'invoice_items', 'data2'));

        if ($input['bill_id'] == @$input['delete_item']) {
            $quote_o->delete();
        } else {
            $quote_o->status = 'approved';
            $quote_o->save();
        }

        //return with successfull message
        //return new RedirectResponse(route('biller.invoices.index'), ['flash_success' => trans('alerts.backend.invoices.created')]);
        echo json_encode(array('status' => 'Success', 'message' => trans('alerts.backend.invoices.created') . ' <a href="' . route('biller.invoices.show', [$result->id]) . '" class="btn btn-primary btn-md"><span class="fa fa-eye" aria-hidden="true"></span> ' . trans('general.view') . '  </a> &nbsp; &nbsp;'));
    }

    public function update_status(ManageQuoteRequest $request)
    {

        $input = $request->only(['bill_id', 'status', 'approved_method', 'approved_by', 'approval_note']);
        $approved_date = date_for_database($request->input('approved_date'));
        $quote_o = Quote::where('id', '=', $input['bill_id'])->first();
        if ($quote_o->id) {

            $quote_o->status = $input['status'];
            $quote_o->approved_method = $input['approved_method'];
            $quote_o->approved_by = $input['approved_by'];
            $quote_o->approval_note = $input['approval_note'];
            $quote_o->approved_date = $approved_date;
            $quote_o->save();
        }
        //return with successfull message
        echo json_encode(array('status' => 'Success', 'message' => trans('general.bill_status_update'), 'bill_status' => trans('payments.' . $input['status'])));
    }

    public function update_lpo(ManageQuoteRequest $request)
    {

        $input = $request->only(['bill_id', 'lpo_number']);
        $lpo_amount = numberClean($request->input('lpo_amount'));
        $lpo_date = date_for_database($request->input('lpo_date'));
        $quote_o = Quote::where('id', '=', $input['bill_id'])->first();
        if ($quote_o->id) {
            $quote_o->lpo_number = $input['lpo_number'];
            $quote_o->lpo_date = $lpo_date;
            $quote_o->lpo_amount = $lpo_amount;
            $quote_o->save();
        }
        //return with successfull message
        echo json_encode(array('status' => 'Success', 'message' => 'Record Updated Successfully ' . $quote_o->id . ' ', 'bill_status' => trans('payments.' . $input['status'])));
    }
}
