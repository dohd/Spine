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
use App\Models\items\VerifiedItem;
use App\Models\lpo\Lpo;
use App\Models\verifiedjcs\VerifiedJc;

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
        
        $segment = array();
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
    public function create()
    {
        return new CreateResponse();
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param StoreInvoiceRequestNamespace $request
     * @return \App\Http\Responses\RedirectResponse
     */
    public function store(CreateQuoteRequest $request)
    {
        // extract request input fields
        $data = $request->only([
            'client_ref', 'tid', 'term_id', 'bank_id', 'invoicedate', 'notes', 'subtotal', 'extra_discount', 
            'currency', 'subtotal', 'tax', 'total', 'tax_format', 'term_id', 'tax_id', 'lead_id', 'attention', 
            'reference', 'reference_date', 'validity', 'pricing', 'prepared_by', 'print_type'
        ]);
        $data_items = $request->only([
            'row_index', 'numbering', 'product_id', 'a_type', 'product_name', 'product_qty', 'product_price', 
            'product_subtotal', 'product_exclusive', 'total_tax', 'unit'
        ]);

        $data['user_id'] = auth()->user()->id;
        $data['ins'] = auth()->user()->ins;

        $result = $this->repository->create(compact('data', 'data_items'));

        $route = route('biller.quotes.index');
        $msg = trans('alerts.backend.quotes.created');
        if ($result['bank_id']) {
            $route = route('biller.quotes.index', 'page=pi');
            $msg = 'Proforma Invoice successfully created';
        }

        return new RedirectResponse($route, ['flash_success' => $msg]);
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
        $data = $request->only([
            'client_ref', 'tid', 'term_id', 'bank_id', 'invoicedate', 'notes', 'subtotal', 'extra_discount', 
            'currency', 'subtotal', 'tax', 'total', 'tax_format', 'revision', 'term_id', 'tax_id', 'lead_id', 
            'attention', 'reference', 'reference_date', 'validity', 'pricing', 'prepared_by', 'print_type'
        ]);
        $data_items = $request->only([
            'row_index', 'item_id', 'numbering', 'a_type', 'product_id', 'product_name', 
            'product_qty', 'product_price', 'product_subtotal', 'unit'
        ]);

        $data['id'] = $quote->id;
        $data['ins'] = auth()->user()->ins;

        $result = $this->repository->update(compact('data', 'data_items'));

        $route = route('biller.quotes.index');
        $msg = trans('alerts.backend.quotes.updated');
        if (isset($result['bank_id'])) {
            $route = route('biller.quotes.index', 'page=pi');
            $msg = 'PI updated successfully';
        }

        return new RedirectResponse($route, ['flash_success' => $msg]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param DeleteQuoteRequestNamespace $request
     * @param App\Models\quote\Quote $quote
     * @return \App\Http\Responses\RedirectResponse
     */
    public function destroy(Quote $quote)
    {
        $this->repository->delete($quote);

        return new RedirectResponse(route('biller.quotes.index'), ['flash_success' => trans('alerts.backend.quotes.deleted')]);
    }


    /**
     * Show the form for viewing the specified resource.
     *
     * @param DeleteQuoteRequestNamespace $request
     * @param App\Models\quote\Quote $quote
     * @return \App\Http\Responses\ViewResponse ViewResponse
     */
    public function show(Quote $quote)
    {
        $products = $quote->products()->orderBy('row_index', 'ASC')->get();
        $accounts = Account::all();
        $features = ConfigMeta::where('feature_id', 9)->first();
        $lpos = Lpo::where('customer_id', $quote->customer_id)->get();

        $quote['bill_type'] = 4;

        $params = array('quote', 'accounts', 'features', 'products', 'lpos');
        return new ViewResponse('focus.quotes.view', compact(...$params));
    }

    /**
     *  List approved project Quotes
     */
    public function project_quotes(ManageQuoteRequest $request)
    {
        $input = $request->only('rel_type', 'rel_id');
        
        $segment = array();
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

        return new ViewResponse('focus.quotes.approved.index', compact('input', 'segment', 'words'));
    }


    /**
     * Show the form for verifying the specified resource.
     *
     * @param string $id
     * @return \App\Http\Responses\Focus\quote\EditResponse
     */
    public function verify(Quote $quote)
    {
        // default Quote items
        $products = $quote->products()->orderBy('row_index')->get();
        $verified_jc = $quote->verified_jcs()->orderBy('verify_no', 'desc')->first();
        // increament verify_no if it exists
        $verify_no = isset($verified_jc) ? $verified_jc->verify_no+1 : 1;
        // fetch verified_items
        if ($verify_no > 1) {
            $products = VerifiedItem::where('quote_id', $quote->id)->orderBy('row_index')->get();
        }

        return view('focus.quotes.approved.verify')
            ->with(compact('quote', 'products', 'verify_no'))
            ->with(bill_helper(2, 4));
    }

    /**
     * Store verified resource in storage.
     *
     * @param \App\Http\Requests\Focus\quote\ManageQuoteRequest $request;
     * @return \App\Http\Responses\RedirectResponse
     */
    public function storeverified(ManageQuoteRequest $request)
    {
        //filter request input fields
        $quote = $request->only(['id', 'verify_no', 'gen_remark', 'total', 'tax', 'subtotal']);
        $quote_items = $request->only([
            'remark', 'row_index', 'item_id', 'a_type', 'numbering', 'product_id', 
            'product_name', 'product_qty', 'product_price', 'product_subtotal', 'unit'
        ]);
        $job_cards = $request->only(['type', 'jcitem_id', 'reference', 'date', 'technician']);

        $result = $this->repository->verify(compact('quote', 'quote_items', 'job_cards'));

        $tid = '';
        if ($result->bank_id) $tid .= 'PI-'.sprintf('%04d', $result->tid);
        else $tid .= 'QT-'.sprintf('%04d', $result->tid);

        return new RedirectResponse(route('biller.quotes.project_quotes'), ['flash_success' => $tid . ' verified successfully']);
    }

    // Fetch Verified Job cards
    public function fetch_verified_jcs($id)
    {
        $verified_jcs = VerifiedJc::where('quote_id', $id)->get();

        return json_encode($verified_jcs);
    }

    // Delete Quote product
    public function delete_product($id)
    {
        $this->repository->delete_product($id);

        return response()->noContent();
    }

    // Delete Verified Quote Item
    public function delete_verified_item($id)
    {
        $this->repository->delete_verified_item($id);

        return response()->noContent();
    }

    // Delete Verified Job card
    public function delete_verified_jcs($id)
    {
        $this->repository->delete_verified_jcs($id);

        return response()->noContent();
    }

    // Reset verified Quote
    public function reset_verified($id)
    {
        // delete verified_items
        VerifiedItem::where('quote_id', $id)->delete();

        $quote = Quote::find($id);
        // delete verified job cards
        $quote->verified_jcs()->delete();
        // reset verified status to No
        $quote->update([
            'verified' => 'No', 
            'verification_date' => null,
            'verified_by' => null,
            'gen_remark' => null
        ]);

        return response()->noContent();
    } 

    // Load Approved Customer Quotes not in any project
    public function customer_quotes()
    {
        $quotes = Quote::whereNotIn('id', function($q) { $q->select('quote_id')->from('project_quotes'); })
            ->where(['customer_id' => request('id'), 'status' => 'approved'])
            ->orderBy('id', 'desc')
            ->get(['id', 'tid', 'notes', 'customer_id', 'bank_id']);

        return response()->json($quotes);
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

    /**
     * Update Quote Approval Status 
     */
    public function approve_quote(ManageQuoteRequest $request, Quote $quote)
    {
        // extract request input fields
        $input = $request->only(['status', 'approved_method', 'approved_by', 'approval_note', 'approved_date']);

        // update
        $input['approved_date'] = date_for_database($input['approved_date']);
        $quote->update($input);

        return new RedirectResponse(route('biller.quotes.show', [$quote]), ['flash_success' => 'Approval status updated successfully']);
    }

    /**
     * Update Quote LPO Details
     */
    public function update_lpo(ManageQuoteRequest $request)
    {
        // extract input fields
        $input = $request->only(['bill_id', 'lpo_id']);

        Quote::find($input['bill_id'])->update(['lpo_id' => $input['lpo_id']]);

        return response()->json(['status' => 'Success', 'message' => 'LPO added successfully', 'refresh' => 1 ]);
    }
}
