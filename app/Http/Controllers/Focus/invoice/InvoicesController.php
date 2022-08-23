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

use App\Http\Controllers\Focus\printer\PrinterController;
use App\Http\Controllers\Focus\printer\RegistersController;
use App\Http\Requests\Focus\invoice\ManagePosRequest;
use App\Models\account\Account;
use App\Models\Company\ConfigMeta;
use App\Models\customer\Customer;
use App\Models\invoice\Draft;
use App\Models\invoice\Invoice;
use App\Models\template\Template;
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
use Illuminate\Support\Facades\Response;
use App\Models\quote\Quote;
use App\Models\project\Project;
use App\Models\bank\Bank;
use App\Models\invoice\PaidInvoice;
use App\Models\lpo\Lpo;
use App\Models\term\Term;
use Bitly;

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

    /**
     * contructor to initialize repository object
     * @param InvoiceRepository $repository ;
     */
    public function __construct(InvoiceRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Display a listing of the resource.
     *
     * @param App\Http\Requests\Focus\invoice\ManageInvoiceRequest $request
     * @return \App\Http\Responses\ViewResponse
     */
    public function index(ManageInvoiceRequest $request)
    {
        return new ViewResponse('focus.invoices.index');
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
        $this->repository->delete($invoice);
        
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
     * Project Invoice index page
     */
    public function project_invoice(ManageInvoiceRequest $request)
    {

        $customers = Customer::where('active', '1')->pluck('company', 'id');
        $lpos = Lpo::distinct('lpo_no')->pluck('lpo_no', 'id');
        $projects = Project::pluck('name', 'id');

        return new ViewResponse('focus.invoices.project_invoice', compact('customers', 'lpos', 'projects'));
    }

    /**
     * Filter invoice quotes and return Create Project Invoice Form
     */
    public function filter_invoice_quotes(Request $request)
    {
        // extract input fields
        $customer_id = $request->customer;
        $quote_ids = explode(',', $request->selected_products);

        if (!$customer_id || !$quote_ids) {
            $customers = Customer::where('active', '1')->pluck('company', 'id');
            $lpos = Lpo::distinct('lpo_no')->pluck('lpo_no', 'id');
            $projects = Project::pluck('name', 'id');
    
            return redirect()->route('biller.invoices.project_invoice')
                ->with(compact('customers', 'lpos', 'projects'));
    
        }
        $quotes = Quote::whereIn('id', $quote_ids)->get();
        $customer = Customer::find($customer_id);

        $accounts = Account::whereHas('accountType', function ($q) {
            $q->whereIn('name', ['Income', 'Other Income']);
        })->with(['accountType' => function ($q) {
            $q->select('id', 'name');
        }])->get();

        // invoice terms
        $terms = Term::where('type', 1)->get();
        $banks = Bank::all();
        $last_tid = Invoice::max('tid');

        $params = compact('quotes', 'customer', 'last_tid', 'banks', 'accounts', 'terms');
        return new ViewResponse('focus.invoices.create_project_invoice', $params);
    }

    /**
     * Store newly created project invoice
     */
    public function store_project_invoice(Request $request)
    {
        // extract request input fields
        $bill = $request->only([
            'customer_id', 'bank_id', 'tax_id', 'tid', 'invoicedate', 'validity', 'notes', 'term_id', 'account_id',
            'subtotal', 'tax', 'total', 
        ]);
        $bill_items = $request->only([
            'description', 'reference', 'unit', 'product_qty', 'product_price', 'quote_id', 'project_id', 'branch_id'
        ]);

        $bill['user_id'] = auth()->user()->id;
        $bill['ins'] = auth()->user()->ins;
        $bill_items = modify_array($bill_items);

        $result = $this->repository->create_project_invoice(compact('bill', 'bill_items'));

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
        // invoice type
        $terms = Term::where('type', 1)->get();

        return new ViewResponse('focus.invoices.edit_project_invoice', compact('invoice', 'banks', 'accounts', 'terms'));
    }

    /**
     * Edit Project Invoice Form
     */
    public function update_project_invoice(Invoice $invoice, Request $request)
    {
        // extract request input fields
        $bill = $request->only([
            'customer_id', 'bank_id', 'tax_id', 'tid', 'invoicedate', 'validity', 'notes', 'term_id', 'account_id',
            'subtotal', 'tax', 'total', 
        ]);
        $bill_items = $request->only([
            'id', 'description', 'reference', 'unit', 'product_qty', 'product_price', 'quote_id', 'project_id', 
            'branch_id'
        ]);

        $bill['user_id'] = auth()->user()->id;
        $bill['ins'] = auth()->user()->ins;

        $bill_items = modify_array($bill_items);

        $result = $this->repository->update_project_invoice($invoice, compact('bill', 'bill_items'));

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
        return new ViewResponse('focus.invoices.index_payment');
    }    

    /**
     * Create invoice payment
     */
    public function create_payment(Request $request)
    {
        $tid = PaidInvoice::max('tid');
        $accounts = Account::whereHas('accountType', function ($q) {
            $q->where('system', 'bank');
        })->get(['id', 'holder']);
        $payments = PaidInvoice::whereColumn('amount', '>', 'allocate_ttl')->get();

        return new ViewResponse('focus.invoices.create_payment', compact('accounts', 'tid', 'payments'));
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

        $data['ins'] = auth()->user()->ins;
        $data['user_id'] = auth()->user()->id;

        // modify and filter paid data items 
        $data_items = modify_array($data_items);
        $data_items = array_filter($data_items, function ($item) { return $item['paid']; });

        $result = $this->repository->create_invoice_payment(compact('data', 'data_items'));

        return new RedirectResponse(route('biller.invoices.index_payment'), ['flash_success' => 'Payment updated successfully']);
    }

    /**
     * Edit invoice payment
     */
    public function edit_payment(PaidInvoice $payment)
    {
        $accounts = Account::whereHas('accountType', function ($q) {
            $q->where('system', 'bank');
        })->get(['id', 'holder']);
        $payments = PaidInvoice::whereColumn('amount', '>', 'allocate_ttl')->get();

        return new ViewResponse('focus.invoices.edit_payment', compact('payment', 'accounts', 'payments'));
    }    

    /**
     * Show invoice payment
     */
    public function show_payment(PaidInvoice $payment)
    {
        return new ViewResponse('focus.invoices.view_payment', compact('payment'));
    }   

    /**
     * Update invoice payment
     */
    public function update_payment(PaidInvoice $payment, Request $request)
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

        $result = $this->repository->update_invoice_payment($payment, compact('data', 'data_items'));

        return new RedirectResponse(route('biller.invoices.index_payment'), ['flash_success' => 'Payment updated successfully']);
    }    

    /**
     * Delete payment from storage
     */
    public function delete_payment($id)
    {
        $this->repository->delete_invoice_payment($id);

        return new RedirectResponse(route('biller.invoices.index_payment'), ['flash_success' => 'Payment deleted successfully']);
    }

    /**
     * Fetch client invoices
     */
    public function client_invoices(Request $request)
    {
        $invoices = Invoice::where('customer_id', $request->id)
            ->whereIn('status', ['due', 'partial'])->get();

        return response()->json($invoices);
    }

    /**
     * Fetch unallocated payments
     */
    public function unallocated_payment(Request $request)
    {
        $pmt = PaidInvoice::where(['customer_id' => $request->customer_id, 'is_allocated' => 0])
            ->with(['account' => function ($q) {
                $q->select(['id', 'holder']);
            }])->first();

        return response()->json($pmt);
    }

    /**
     * Print invoice payment receipt
     */
    public function print_payment(PaidInvoice $paidinvoice)
    {
        $html = view('focus.invoices.print_payment', ['resource' => $paidinvoice])->render();
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
}
