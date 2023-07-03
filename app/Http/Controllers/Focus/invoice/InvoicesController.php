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

namespace App\Http\Controllers\Focus\invoice;

use App\Http\Controllers\Focus\printer\RegistersController;
use App\Http\Requests\Focus\invoice\ManagePosRequest;
use App\Models\account\Account;
use App\Models\Company\ConfigMeta;
use App\Models\customer\Customer;
use App\Models\invoice\Invoice;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Responses\ViewResponse;
use App\Http\Responses\Focus\invoice\CreateResponse;
use App\Http\Responses\Focus\invoice\EditResponse;
use App\Repositories\Focus\invoice\InvoiceRepository;
use App\Http\Requests\Focus\invoice\ManageInvoiceRequest;
use App\Http\Requests\Focus\invoice\CreateInvoiceRequest;
use App\Http\Requests\Focus\invoice\EditInvoiceRequest;
use App\Http\Responses\RedirectResponse;
use App\Models\additional\Additional;
use Illuminate\Support\Facades\Response;
use App\Models\quote\Quote;
use App\Models\project\Project;
use App\Models\bank\Bank;
use App\Models\Company\Company;
use App\Models\currency\Currency;
use App\Models\invoice_payment\InvoicePayment;
use App\Models\lpo\Lpo;
use App\Models\term\Term;
use App\Models\verification\Verification;
use App\Repositories\Focus\invoice_payment\InvoicePaymentRepository;
use App\Repositories\Focus\pos\PosRepository;
use Endroid\QrCode\QrCode;
use Error;
use Illuminate\Validation\ValidationException;
use Storage;

/**
 * InvoicesController
 */
class InvoicesController extends Controller
{
    /**
     * variable to store the repository object
     * @var InvoiceRepository
     */
    protected $repository;
    protected $pos_repository;
    protected $inv_payment_repository;

    /**
     * contructor to initialize repository object
     * @param InvoiceRepository $repository ;
     */
    public function __construct(
        InvoiceRepository $repository, 
        PosRepository $pos_repository, 
        InvoicePaymentRepository $inv_payment_repository
    )
    {
        $this->repository = $repository;
        $this->pos_repository = $pos_repository;
        $this->inv_payment_repository = $inv_payment_repository;
    }

    /**
     * Display a listing of the resource.
     *
     * @param App\Http\Requests\Focus\invoice\ManageInvoiceRequest $request
     * @return \App\Http\Responses\ViewResponse
     */
    public function index(ManageInvoiceRequest $request)
    {
        $customers = Customer::whereHas('invoices')->get(['id', 'company']);
        
        return new ViewResponse('focus.invoices.index', compact('customers'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @param CreateInvoiceRequestNamespace $request
     * @return \App\Http\Responses\Focus\invoice\CreateResponse
     */
    public function create(CreateInvoiceRequest $request)
    {
        return new CreateResponse('focus.invoices.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param StoreInvoiceRequestNamespace $request
     * @return \App\Http\Responses\RedirectResponse
     */
    public function store(CreateInvoiceRequest $request)
    {
        dd($request->all());
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param App\Models\invoice\Invoice $invoice
     * @param EditInvoiceRequestNamespace $request
     * @return \App\Http\Responses\Focus\invoice\EditResponse
     */
    public function edit(Invoice $invoice, EditInvoiceRequest $request)
    {
        return new EditResponse($invoice);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param DeleteInvoiceRequestNamespace $request
     * @param App\Models\invoice\Invoice $invoice
     * @return \App\Http\Responses\RedirectResponse
     */
    public function destroy(Invoice $invoice)
    {
        try {
            $this->repository->delete($invoice);
        } catch (\Throwable $th) {
            if ($th instanceof ValidationException) throw $th;
            return errorHandler('Error Deleting Invoice', $th);
        }
        
        return new RedirectResponse(route('biller.invoices.index'), ['flash_success' => trans('alerts.backend.invoices.deleted')]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param DeleteInvoiceRequestNamespace $request
     * @param App\Models\invoice\Invoice $invoice
     * @return \App\Http\Responses\RedirectResponse
     */
    public function show(Invoice $invoice, ManageInvoiceRequest $request)
    {
        $accounts = Account::all();
        $features = ConfigMeta::where('feature_id', 9)->first();
        $invoice['bill_type'] = 1;
        $words = [
            'prefix' => '',
            'paynote' => trans('invoices.payment_for_invoice') . ' '. '#' . $invoice->tid
        ];
        
        return new ViewResponse('focus.invoices.view', compact('invoice', 'accounts', 'features', 'words'));
    }    

    /**
     * Uninvoiced quotes
     */
    public function uninvoiced_quote(ManageInvoiceRequest $request)
    {
        $customers = Customer::whereHas('quotes', fn($q) => $q->where(['verified' => 'Yes', 'invoiced' => 'No']))
            ->get(['id', 'company']);
        $lpos = Lpo::whereHas('quotes', fn($q) =>  $q->where(['verified' => 'Yes', 'invoiced' => 'No']))
            ->get(['id', 'lpo_no', 'customer_id']);
        $projects = Project::whereHas('quote', fn($q) => $q->where(['verified' => 'Yes', 'invoiced' => 'No']))
            ->get(['id', 'name', 'customer_id']);

        return new ViewResponse('focus.invoices.uninvoiced_quote', compact('customers', 'lpos', 'projects'));
    }

    /**
     * Filter Invoice Products and redirect to Invoice Form
     */
    public function filter_invoice_quotes(Request $request)
    { 
        $customer_id = $request->customer;
        $quote_ids = explode(',', $request->selected_products);
        if (!$customer_id || !$quote_ids) {
            $customers = Customer::whereHas('quotes', fn($q) => $q->where(['verified' => 'Yes', 'invoiced' => 'No']))
                ->get(['id', 'company']);
            $lpos = Lpo::whereHas('quotes', fn($q) =>  $q->where(['verified' => 'Yes', 'invoiced' => 'No']))
                ->get(['id', 'lpo_no', 'customer_id']);
            $projects = Project::whereHas('quote', fn($q) => $q->where(['verified' => 'Yes', 'invoiced' => 'No']))
                ->get(['id', 'name', 'customer_id']);

            return view($request->is_part_verification? 'focus.verifications.index' : 'focus.invoices.uninvoiced_quote', compact('customers', 'lpos', 'projects'))
                ->with(['flash_error' => 'Filter and select customer records']);
        }

        if ($request->is_part_verification) {
            // Quote/PI in order of selection (partial verification)
            $quotes = Verification::whereIn('id', $quote_ids)
                ->orderByRaw("FIELD(id,{$request->selected_products})")
                ->with(['items' => fn($q) =>$q->orderBy('row_index', 'ASC')])
                ->with(['branch', 'customer'])
                ->get()
                ->map(function($v) {
                    // mimic quote by assigning quote attributes
                    $v->verification_id = $v->id;
                    $v->title = @$v->quote->notes;
                    $v->bank_id = @$v->quote->bank_id;
                    $v->tid = @$v->quote->tid;
                    $v->client_ref = @$v->quote->client_ref;
                    $v->lpo = @$v->quote->lpo;
                    $v->project_quote = @$v->project_quote;
                    $v->verified_products = $v->items;
                    $v->verified_jcs = $v->jc_items;
                    return $v;
                });
        } else {
            // Quote/PI in order of selection (main verification)
            $quotes = Quote::whereIn('id', $quote_ids)
                ->orderByRaw("FIELD(id,{$request->selected_products})")
                ->with(['verified_products' => fn($q) =>$q->orderBy('row_index', 'ASC')])
                ->get();
        }

        $customer = Customer::find($customer_id) ?: new Customer;
        $accounts = Account::whereHas('accountType', fn($q) => $q->whereIn('name', ['Income', 'Other Income']))->get();
        $terms = Term::where('type', 1)->get();  // invoice term type is 1
        $banks = Bank::all();
        $additionals = Additional::all();

        $ins =  auth()->user()->ins;
        $last_tid = Invoice::where('ins', $ins)->max('tid');
        $prefixes = prefixesArray(['invoice', 'quote', 'proforma_invoice', 'purchase_order', 'delivery_note', 'jobcard'], $ins);

        return new ViewResponse('focus.invoices.create_project_invoice',
            compact('quotes', 'customer', 'last_tid', 'banks', 'accounts', 'terms', 'quote_ids', 'additionals', 'prefixes'),
        );
    }

    /**
     * Store newly created project invoice
     */
    public function store_project_invoice(Request $request)
    {  
        // extract request input fields
        $bill = $request->only([
            'customer_id', 'bank_id', 'tax_id', 'tid', 'invoicedate', 'validity', 'notes', 'term_id', 'account_id',
            'taxable', 'subtotal', 'tax', 'total', 
        ]);
        $bill_items = $request->only([
            'numbering', 'row_index', 'description', 'reference', 'unit', 'product_qty', 'product_subtotal', 'product_price', 
            'tax_rate', 'quote_id', 'project_id', 'branch_id','verification_id'
        ]);

        $bill['user_id'] = auth()->user()->id;
        $bill['ins'] = auth()->user()->ins;
        $bill_items = modify_array($bill_items);

        try {
            $result = $this->repository->create_project_invoice(compact('bill', 'bill_items'));
        } catch (\Throwable $th) {
            if ($th instanceof ValidationException) throw $th;
            return errorHandler('Error Creating Project Invoice', $th);
        }

        // print preview
        $valid_token = token_validator('', 'i' . $result->id . $result->tid, true);
        $msg = ' <a href="'. route( 'biller.print_bill',[$result->id, 1, $valid_token, 1]) .'" class="invisible" id="printpreview"></a>'; 
        
        return new RedirectResponse(route('biller.invoices.index'), ['flash_success' => 'Project Invoice created successfully' . $msg]);
    }

    /**
     * Edit Project Invoice Form
     */
    public function edit_project_invoice(Invoice $invoice)
    {
        $banks = Bank::all();
        $accounts = Account::whereHas('accountType', function ($query) {
            $query->whereIn('name', ['Income', 'Other Income']);
        })->with(['accountType' => function ($query) {
            $query->select('id', 'name');
        }])->get();

        $terms = Term::where('type', 1)->get(); // invoice type 1
        $additionals = Additional::all();
        $prefixes = prefixesArray(['invoice'], $invoice->ins);

        $params = compact('invoice', 'banks', 'accounts', 'terms', 'additionals', 'prefixes');

        return new ViewResponse('focus.invoices.edit_project_invoice', $params);
    }

    /**
     * Edit Project Invoice Form
     */
    public function update_project_invoice(Invoice $invoice, Request $request)
    {
        // extract request input fields
        $bill = $request->only([
            'customer_id', 'bank_id', 'tax_id', 'tid', 'invoicedate', 'validity', 'notes', 'term_id', 'account_id',
            'taxable', 'subtotal', 'tax', 'total', 
        ]);
        $bill_items = $request->only([
            'id', 'numbering', 'row_index', 'description', 'reference', 'unit', 'product_qty', 
            'product_subtotal', 'product_price', 'tax_rate', 'quote_id', 'project_id', 'branch_id',
            'verification_id'
        ]);

        $bill['user_id'] = auth()->user()->id;
        $bill['ins'] = auth()->user()->ins;

        $bill_items = modify_array($bill_items);

        try {
            $result = $this->repository->update_project_invoice($invoice, compact('bill', 'bill_items'));
        } catch (\Throwable $th) { 
            if ($th instanceof ValidationException) throw $th;
            return errorHandler('Error Updating Project Invoice', $th);
        }

        // print preview
        $valid_token = token_validator('', 'i' . $result->id . $result->tid, true);
        $msg = ' <a href="'. route( 'biller.print_bill',[$result->id, 1, $valid_token, 1]) .'" class="invisible" id="printpreview"></a>'; 

        return new RedirectResponse(route('biller.invoices.index'), ['flash_success' => 'Project Invoice Updated successfully' . $msg]);
    }


    /**
     * Create invoice payment
     */
    public function index_payment(Request $request)
    {
        $customers = Customer::get(['id', 'company']);

        return new ViewResponse('focus.invoices.index_payment', compact('customers'));
    }    

    /**
     * Create invoice payment
     */
    public function create_payment(Request $request)
    {
        $tid = InvoicePayment::where('ins', auth()->user()->ins)->max('tid');

        $accounts = Account::whereHas('accountType', function ($q) {
            $q->where('system', 'bank');
        })->get(['id', 'holder']);

        $unallocated_pmts = InvoicePayment::whereIn('payment_type', ['on_account', 'advance_payment'])
            ->whereColumn('amount', '!=', 'allocate_ttl')
            ->orderBy('date', 'asc')->get();

        return new ViewResponse('focus.invoices.create_payment', compact('accounts', 'tid', 'unallocated_pmts'));
    }

    /**
     * Store invoice payment
     */
    public function store_payment(Request $request)
    {
        // extract request input
        $data = $request->only([
            'account_id', 'customer_id', 'date', 'tid', 'deposit', 'amount', 'allocate_ttl',
            'payment_mode', 'reference', 'payment_id', 'payment_type'
        ]);
        $data_items = $request->only(['invoice_id', 'paid']); 
        $data_items = modify_array($data_items);

        $data['ins'] = auth()->user()->ins;
        $data['user_id'] = auth()->user()->id;

        try {
            $result = $this->inv_payment_repository->create(compact('data', 'data_items'));
        } catch (\Throwable $th) {
            return errorHandler('Error Updating Payment', $th);
        }

        return new RedirectResponse(route('biller.invoices.index_payment'), ['flash_success' => 'Payment updated successfully']);
    }

    /**
     * Edit invoice payment
     */
    public function edit_payment(InvoicePayment $payment)
    {   
        $accounts = Account::whereHas('accountType', function ($q) {
            $q->where('system', 'bank');
        })->get(['id', 'holder']);
        $unallocated_pmts = InvoicePayment::whereIn('payment_type', ['on_account', 'advance_payment'])
            ->whereColumn('amount', '!=', 'allocate_ttl')
            ->orderBy('date', 'asc')->get();

        return new ViewResponse('focus.invoices.edit_payment', compact('payment', 'accounts', 'unallocated_pmts'));
    }    

    /**
     * Show invoice payment
     */
    public function show_payment(InvoicePayment $payment)
    {
        return new ViewResponse('focus.invoices.view_payment', compact('payment'));
    }   

    /**
     * Update invoice payment
     */
    public function update_payment(InvoicePayment $payment, Request $request)
    {
        // extract request input
        $data = $request->only([
            'account_id', 'customer_id', 'date', 'tid', 'deposit', 'amount', 'allocate_ttl',
            'payment_mode', 'reference', 'payment_id', 'payment_type'
        ]);
        $data_items = $request->only(['id', 'invoice_id', 'paid']); 

        $data['ins'] = auth()->user()->ins;
        $data['user_id'] = auth()->user()->id;
        $data_items = modify_array($data_items);

        try {
            $result = $this->inv_payment_repository->update($payment, compact('data', 'data_items'));
        } catch (\Throwable $th) { dd($th);
            return errorHandler('Error Updating Payment', $th);
        }

        return new RedirectResponse(route('biller.invoices.index_payment'), ['flash_success' => 'Payment updated successfully']);
    }    

    /**
     * Delete payment from storage
     */
    public function delete_payment($id)
    {
        $payment = InvoicePayment::find($id);
        try {
            $this->inv_payment_repository->delete($payment);
        } catch (\Throwable $th) {
            return errorHandler('Error Deleting Payment', $th);
        }

        return new RedirectResponse(route('biller.invoices.index_payment'), ['flash_success' => 'Payment deleted successfully']);
    }

    /**
     * Fetch client invoices
     */
    public function client_invoices(Request $request)
    {
        $w = $request->search;
        $query = Invoice::where('customer_id', $request->customer_id)
            ->where('currency_id', 1)->whereIn('status', ['due', 'partial']);
            
        if ($w) $invoices = $query->where('notes', 'LIKE', "%{$w}%")->orderBy('invoiceduedate', 'asc')->limit(6)->get();
        else $invoices = $query->orderBy('invoiceduedate', 'asc')->get();
            
        return response()->json($invoices);
    }

    /**
     * Fetch unallocated payments
     */
    public function unallocated_payment(Request $request)
    {
        $pmt = InvoicePayment::where(['customer_id' => $request->customer_id, 'is_allocated' => 0])
            ->with(['account' => function ($q) {
                $q->select(['id', 'holder']);
            }])->first();

        return response()->json($pmt);
    }

    /**
     * Print invoice payment receipt
     */
    public function print_payment(InvoicePayment $paidinvoice)
    {
        $company = Company::find(auth()->user()->ins) ?: new Company;
        
        $html = view('focus.invoices.print_payment', ['resource' => $paidinvoice, 'company' => $company])->render();
        $pdf = new \Mpdf\Mpdf(config('pdf'));
        $pdf->WriteHTML($html);
        $headers = array(
            "Content-type" => "application/pdf",
            "Pragma" => "no-cache",
            "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
            "Expires" => "0"
        );

        return Response::stream($pdf->Output('payment.pdf', 'I'), 200, $headers);
    }        

    /**
     * POS Create 
     */
    public function pos(ManagePosRequest $request, RegistersController $register)
    {
        if (!$register->status()) return view('focus.invoices.pos.open_register');

        $tid = Invoice::where('ins', auth()->user()->ins)->max('tid');
        $customer = Customer::first();
        $currencies = Currency::all();
        $terms = Term::all();
        $additionals = Additional::all();
        $defaults = ConfigMeta::get()->groupBy('feature_id');
        
        $pos_account = Account::where('system', 'pos')->first(['id', 'holder']);
        $accounts = Account::where('account_type', 'Asset')
            ->whereHas('accountType', fn($q) => $q->where('system', 'bank'))
            ->get(['id', 'holder', 'number']);
        
        $params = compact('customer', 'accounts', 'pos_account', 'tid', 'currencies', 'terms', 'additionals', 'defaults');
        return view('focus.invoices.pos.create', $params)->with(product_helper());
    }

    /**
     * POS Store 
     */
    public function pos_store(CreateInvoiceRequest $request)
    {
        $request->validate(['customer_id' => 'required']);
        if (count(array_filter($request->only('is_pay', 'pmt_reference', 'p_account'))) < 3) {
            throw ValidationException::withMessages(['Payment Reference and Payment Account required']);
        }
        
        try {
            $result = $this->pos_repository->create($request->except('_token'));
        } catch (\Throwable $th) {
            if ($request->ajax()) {
                return response()->json([
                    'status' => 'Error', 
                    'message' => 'Error Processing POS Transaction! Try again later',
                    'error_message' => $th->getMessage(),
                ]);
            }
            return errorHandler('Error Creating POS Transaction', $th);
        }
        
        return response()->json([
            'status' => 'Success', 
            'message' => 'POS Transaction Done Successfully',
            'invoice' => $result,
        ]);
    }

    /**
     * TIMS KIT Api Call: Electronic Tax Register (ETR) Invoice
     */
    public function attach_etr()
    {
        $company = Company::find(auth()->user()->ins); 
        if (!$company->etr_invoice_endpoint) throw new Error('ETR invoice endpoint not set!');

        $invoice = Invoice::find(request('invoice_id'));
        if (!array_key_exists('etr_url', $invoice->toArray())) throw new Error('ETR invoice url field not set!');
        if (!array_key_exists('etr_qrcode', $invoice->toArray())) throw new Error('ETR invoice QRcode field not set!');
            
        $payload = config('datecs_etr.invoice');

        if ($invoice->customer) {
            $customer = $invoice->customer;
            $payload['buyer'] = [
                'buyerAddress' => 'string',
                'buyerName' => $customer->company,
                'buyerPhone' => $customer->phone,
                'pinOfBuyer' => 'P123456789P',
            ];
        }
        $payload['items'][0]['unitPrice'] = +$invoice->total;
        $payload['payment'][0]['amount'] = +$invoice->total;
        
        try {
            $client = new \GuzzleHttp\Client();
            $client_resp = $client->post($company->etr_invoice_endpoint, [
                'headers' => [
                    'Content-Type' => "application/json",
                    'Accept' => "application/json",
                ],
                'json' => $payload,
            ]);
            $data = json_decode($client_resp->getBody()->getContents());

            if ($data->messages == 'Success' && isset($data->verificationUrl)) {
                // extract invoice no
                $query = parse_url($data->verificationUrl, PHP_URL_QUERY);
                parse_str($query, $params);
                $invoice_no = $params['invoiceNo'];
                // generate QR code
                $timestamp = date('Y_m_d_H_i_s');
                $filename = "invoice_{$invoice_no}_{$timestamp}.png";
                $qrCode = new QrCode($data->verificationUrl);
                $qrCode->writeFile(Storage::disk('public')->path("qr".DIRECTORY_SEPARATOR."{$filename}"));
                // update invoice
                $invoice->update(['etr_url' => $data->verificationUrl, 'etr_qrcode' => $filename]);
            }

            return (array) $data;
        } catch (\Throwable $th) {
            printlog($th->getMessage());
            throw new Error('ETR Processing error!');
        }
    }
}
