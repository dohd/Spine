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

use App\Models\quote\Quote;
use App\Http\Controllers\Controller;
use App\Http\Responses\RedirectResponse;
use App\Http\Responses\ViewResponse;
use App\Http\Responses\Focus\quote\CreateResponse;
use App\Http\Responses\Focus\quote\EditResponse;
use App\Repositories\Focus\quote\QuoteRepository;
use App\Http\Requests\Focus\quote\ManageQuoteRequest;
use App\Models\account\Account;
use App\Models\Company\ConfigMeta;
use App\Http\Requests\Focus\quote\CreateQuoteRequest;
use App\Http\Requests\Focus\quote\EditQuoteRequest;
use App\Models\customer\Customer;
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
        $customers = Customer::all(['id', 'company']);
        
        return new ViewResponse('focus.quotes.index', compact('customers'));
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
            'client_ref', 'tid', 'date', 'notes', 'subtotal', 'tax', 'total', 
            'currency_id', 'term_id', 'tax_id', 'lead_id', 'pricegroup_id', 'attention',
            'reference', 'reference_date', 'validity', 'prepared_by', 'print_type', 
            'customer_id', 'branch_id', 'bank_id', 'is_repair'
        ]);
        $data_items = $request->only([
            'numbering', 'product_id', 'product_name', 'product_qty', 'product_subtotal', 'product_price', 
            'unit', 'estimate_qty', 'buy_price', 'row_index', 'a_type', 'misc'
        ]);
        $skill_items = $request->only(['skill', 'charge', 'hours', 'no_technician' ]);
            
        $data['user_id'] = auth()->user()->id;
        $data['ins'] = auth()->user()->ins;

        $data_items = modify_array($data_items);
        $skill_items = modify_array($skill_items);

        $result = $this->repository->create(compact('data', 'data_items', 'skill_items'));

        $route = route('biller.quotes.index');
        $msg = trans('alerts.backend.quotes.created');
        if ($result['bank_id']) {
            $route = route('biller.quotes.index', 'page=pi');
            $msg = 'Proforma Invoice created successfully';
        }

        // print preview url
        $valid_token = token_validator('', 'q'.$result->id .$result->tid, true);
        $msg .= ' <a href="'. route('biller.print_quote', [$result->id, 4, $valid_token, 1]) .'" class="invisible" id="printpreview"></a>'; 

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
        // extract request input fields
        $data = $request->only([
            'client_ref', 'tid', 'date', 'notes', 'subtotal', 'tax', 'total', 
            'currency_id', 'term_id', 'tax_id', 'lead_id', 'pricegroup_id', 'attention',
            'reference', 'reference_date', 'validity', 'prepared_by', 'print_type', 
            'customer_id', 'branch_id', 'bank_id', 'revision', 'is_repair'
        ]);
        $data_items = $request->only([
            'id', 'numbering', 'product_id', 'product_name', 'product_qty', 'product_subtotal', 'product_price', 
            'unit', 'estimate_qty', 'buy_price', 'row_index', 'a_type', 'misc'
        ]);
        $skill_items = $request->only(['skill_id', 'skill', 'charge', 'hours', 'no_technician' ]);

        $data['user_id'] = auth()->user()->id;
        $data['ins'] = auth()->user()->ins;

        $data_items = modify_array($data_items);
        $skill_items = modify_array($skill_items);

        $result = $this->repository->update($quote, compact('data', 'data_items', 'skill_items'));

        $route = route('biller.quotes.index');
        $msg = trans('alerts.backend.quotes.updated');
        if ($result['bank_id']) {
            $route = route('biller.quotes.index', 'page=pi');
            $msg = 'Proforma Invoice updated successfully';
        }

        // print preview url
        $valid_token = token_validator('', 'q'.$result->id .$result->tid, true);
        $msg .= ' <a href="'. route('biller.print_quote', [$result->id, 4, $valid_token, 1]) .'" class="invisible" id="printpreview"></a>';        

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
        $res = $this->repository->delete($quote);

        $link = route('biller.quotes.index');
        $msg = trans('alerts.backend.quotes.deleted');
        if ($res == 'PI') {
            $link = route('biller.quotes.index', 'page=pi');
            $msg = 'The Proforma Invoice was successfully deleted';
        }

        return new RedirectResponse($link, ['flash_success' => $msg]);
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
        $quote['bill_type'] = 4;
        $accounts = Account::all();
        $features = ConfigMeta::where('feature_id', 9)->first();
        $lpos = Lpo::where('customer_id', $quote->customer_id)->get();

        return new ViewResponse('focus.quotes.view', compact('quote', 'accounts', 'features', 'lpos'));
    }

    /**
     * Close quote
     */
    public function close_quote(Quote $quote)
    {
        $data['user_id'] = auth()->user()->id;
        $data['ins'] = auth()->user()->ins;
        
        $this->repository->close_quote($quote, $data);

        $resource = $quote->bank_id ? 'Proforma Invoice' : 'Quote';
        
        return redirect()->back()->with(['flash_success' => $resource . ' closed successfully']);

    }

    /**
     *  Fetch verify quotes
     */
    public function get_verify_quote(ManageQuoteRequest $request)
    {
        return new ViewResponse('focus.quotesverify.index');
    }

    /**
     * Show the form for verifying the specified resource.
     *
     * @param string $id
     * @return \App\Http\Responses\Focus\quote\EditResponse
     */
    public function verify_quote(Quote $quote)
    {
        $products = VerifiedItem::where('quote_id', $quote->id)->get();
        $jobcards = VerifiedJc::where('quote_id', $quote->id)->with('equipment')->get();
        if (!$products->count()) $products = $quote->products;

        return new ViewResponse('focus.quotesverify.create', compact('quote', 'products', 'jobcards') + bill_helper(2, 4));
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
        $data = $request->only(['id', 'verify_no', 'gen_remark', 'total', 'tax', 'subtotal']);
        $data_items = $request->only([
            'remark', 'row_index', 'item_id', 'a_type', 'numbering', 'product_id', 
            'product_name', 'product_qty', 'product_price', 'product_subtotal', 'unit'
        ]);
        $job_cards = $request->only(['type', 'jcitem_id', 'reference', 'date', 'technician', 'equipment_id', 'fault']);

        $data_items = modify_array($data_items);
        $job_cards = modify_array($job_cards);

        $result = $this->repository->verify(compact('data', 'data_items', 'job_cards'));

        $tid = $result->tid;
        if ($result->bank_id) $tid = gen4tid('PI-', $tid);
        else $tid = gen4tid('QT-', $tid);

        return new RedirectResponse(route('biller.quotes.get_verify_quote'), ['flash_success' => $tid . ' verified successfully']);
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

    /**
     * Approved Customer Quotes not in any project
     */
    public function customer_quotes()
    {
        $quotes = Quote::with('branch')
            ->where(['customer_id' => request('id'), 'status' => 'approved'])
            ->whereNull('project_quote_id')
            ->orderBy('id', 'desc')
            ->get();

        return response()->json($quotes);
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
