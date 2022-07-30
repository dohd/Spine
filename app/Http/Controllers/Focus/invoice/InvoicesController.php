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
        $accounts = Account::whereHas('accountType', function ($query) {
            $query->whereIn('name', ['Income', 'Other Income']);
        })->with(['accountType' => function ($query) {
            $query->select('id', 'name');
        }])->get();
        $banks = Bank::all();
        $last_tid = Invoice::max('tid');
        // invoice type
        $terms = Term::where('type', 1)->get();

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
     * Delete Payment from storage
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
     * Fetch unallocated payment
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
     * Print payment
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


    
    public function print_document(Invoice $invoice, ManageInvoiceRequest $request)
    {
        $invoice = $this->repository->find($request->id);
        switch ($request->type) {
            case 1:
                //delivery note
                $general = array(
                    'bill_type' => trans('invoices.delivery_note'),
                    'lang_bill_number' => trans('invoices.delivery_note'),
                    'lang_bill_date' => trans('invoices.invoice_date'),
                    'lang_bill_due_date' => trans(
                        'invoices.invoice_due_date'
                    ), 'direction' => 'rtl',
                    'person' => trans('customers.customer'),
                    'prefix' => 1
                );
                $html = view('focus.bill.delivery', compact('invoice', 'general'))->render();
                $name = 'delivery_note_' . $invoice->tid . '.pdf';
                break;
            case 2:
                //delivery note
                $general = array(
                    'bill_type' => trans('invoices.delivery_note'),
                    'lang_bill_number' => trans('invoices.delivery_note'),
                    'lang_bill_date' => trans('invoices.invoice_date'),
                    'lang_bill_due_date' => trans(
                        'invoices.invoice_due_date'
                    ), 'direction' => 'rtl',
                    'person' => trans('customers.customer'),
                    'prefix' => 2
                );

                $html = view('focus.bill.proforma', compact('invoice', 'general'))->render();
                $name = 'delivery_note_' . $invoice->tid . '.pdf';
                break;
        }
        $pdf = new \Mpdf\Mpdf(config('pdf'));
        $pdf->WriteHTML($html);
        $headers = array(
            "Content-type" => "application/pdf",
            "Pragma" => "no-cache",
            "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
            "Expires" => "0"
        );
        return Response::stream($pdf->Output($name, 'I'), 200, $headers);
    }

    public function update_status(Request $request)
    {
        switch ($request->bill_type) {
            case 1:
                $result = Invoice::where('id', $request->bill_id)->update(array('status' => $request->status));
                if ($result) echo json_encode(array('status' => 'Success', 'message' => trans('alerts.bills.updated'), 'bill_status' => trans('payments.' . $request->status)));
                break;
            case 2:
                $result = Invoice::where('id', $request->bill_id)->update(array('i_class' => $request->status));
                if ($result) echo json_encode(array('status' => 'Success', 'message' => trans('alerts.bills.updated'), 'bill_status' => trans('payments.' . $request->status)));
                break;
        }
    }

    public function pos(ManagePosRequest $request, RegistersController $register)
    {
        if ($register->status()) {
            $input = $request->only(['sub', 'p']);
            $customer = Customer::first();
            $accounts = Account::all();

            $input['sub'] = false;
            $last_invoice = Invoice::where('i_class', '=', 1)->latest()->first();

            return view('focus.invoices.pos.create')->with(array('last_invoice' => $last_invoice, 'sub' => $input['sub'], 'p' => $request->p, 'accounts' => $accounts, 'customer' => $customer))->with(bill_helper(1, 2))->with(product_helper());
        } else {
            return view('focus.invoices.pos.open_register');
        }
    }

    public function pos_store(ManagePosRequest $request, PrinterController $printer)
    {

        //Input received from the request
        $invoice = $request->only(['customer_id', 'tid', 'refer', 'invoicedate', 'invoiceduedate', 'recur_after', 'sub', 'notes', 'subtotal', 'shipping', 'tax', 'discount', 'discount_rate', 'after_disc', 'currency', 'total', 'tax_format', 'discount_format', 'ship_tax', 'ship_tax_type', 'ship_rate', 'term_id', 'tax_id', 'p']);

        $invoice_items = $request->only(['product_id', 'product_name', 'code', 'product_qty', 'product_price', 'product_tax', 'product_discount', 'product_subtotal', 'product_subtotal', 'total_tax', 'total_discount', 'product_description', 'unit', 'serial', 'unit_m']);

        $invoice_payment = $request->only(['p_amount', 'p_method', 'p_account', 'b_change']);
        $data2 = $request->only(['custom_field']);
        $data2['ins'] = auth()->user()->ins;
        //dd($invoice_items);
        $invoice['ins'] = auth()->user()->ins;
        $invoice['user_id'] = auth()->user()->id;
        $invoice_items['ins'] = auth()->user()->ins;
        //Create the model using repository create method
        $invoice['i_class'] = 1;
        $result = $this->repository->create(compact('invoice', 'invoice_items', 'data2'));
        if ($result) {
            if (isset($result['id'])) $pay = $this->repository->payment($result, $invoice_payment);
            //return with successfull message
            $valid_token = token_validator('', 'i' . $result['id'] . $result['tid'], true);
            $link = route('biller.print_bill', [$result['id'], 1, $valid_token, 1]);
            $link_download = route('biller.print_bill', [$result['id'], 1, $valid_token, 2]);
            $link_preview = route('biller.view_bill', [$result['id'], 1, $valid_token, 0]);
            $lk = '';
            $out = '';
            if (feature(19)->feature_value == 1) $out = $printer->thermal_print($result);
            if (session('d_id')) {
                Draft::find(session('d_id'))->delete();
                session()->forget('d_id');
            }
            if (isset($result['p'])) $lk .= '<a href="' . route('biller.projects.show', [$result['p']]) . '" class="btn btn-info btn-md"><span class="fa fa-repeat" aria-hidden="true"></span> ' . trans('invoices.return_project') . '  </a> ';
            if ($pay) echo json_encode(array('status' => 'Success', 'message' => trans('alerts.backend.invoices.created') . ' <a href="' . route('biller.invoices.show', [$result->id]) . '" class="btn btn-info btn-md"><span class="fa fa-eye" aria-hidden="true"></span> ' . trans('general.view') . '  </a> <a href="' . $link . '" class="btn btn-purple btn-md"><span class="fa fa-print" aria-hidden="true"></span> ' . trans('general.print') . '  </a> <a href="' . $link_download . '" class="btn btn-warning btn-md"><span class="fa fa-file-pdf-o" aria-hidden="true"></span> ' . trans('general.pdf') . '  </a> <a href="' . $link_preview . '" class="btn btn-purple btn-md"><span class="fa fa-globe" aria-hidden="true"></span> ' . trans('general.preview') . '  </a> <a href="' . route('biller.invoices.pos') . '" class="btn btn-blue-grey btn-md"><span class="fa fa-plus-circle" aria-hidden="true"></span> ' . trans('general.create') . '  </a> ' . $lk . ' &nbsp; &nbsp;<br>' . $out, 'd_id' => $result['id']));
            $feature = feature(11);
            $alert = json_decode($feature->value2, true);
            if ($alert['new_invoice'] or $alert['cust_new_invoice']) {
                $template = Template::all()->where('category', '=', 1)->where('other', '=', 1)->first();
                $valid_token = token_validator('', 'i' . $result['id'] . $result['tid'], true);
                $link = route('biller.view_bill', [$result->id, 1, $valid_token, 0]);
                $data = array(
                    'Company' => config('core.cname'),
                    'BillNumber' => $result->tid,
                    'BillType' => trans('invoices.invoice'),
                    'URL' => "<a href='$link'>$link</a>",
                    'Name' => $result->customer->name,
                    'CompanyDetails' => '<h6><strong>' . config('core.cname') . ',</strong></h6>
<address>' . config('core.address') . '<br>' . config('core.city') . '</address>
            ' . config('core.region') . ' : ' . config('core.country') . '<br>  ' . trans('general.email') . ' : ' . config('core.email'),
                    'DueDate' => dateFormat(date('Y-m-d')),
                    'Amount' => amountFormat($result->total)
                );
                $replaced_body = parse($template['body'], $data, true);
                $subject = parse($template['title'], $data, true);
                $mail = array();
                if ($alert['new_invoice'] and !$alert['cust_new_invoice']) {
                    $mail['mail_to'] = $feature->value1;
                } elseif ($alert['cust_new_invoice'] and !$alert['new_invoice']) {
                    $mail['mail_to'] = $result->customer->email;
                } else {
                    $mail['mail_to'][] = $result->customer->email;
                    $mail['mail_to'][] = $feature->value1;
                }
                $mail['customer_name'] = trans('transactions.transaction');
                $mail['subject'] = $subject;
                $mail['text'] = $replaced_body;
                business_alerts($mail);
            }
            if ($alert['sms_new_invoice']) {
                $template = Template::all()->where('category', '=', 2)->where('other', '=', 11)->first();
                $valid_token = token_validator('', 'i' . $result['id'] . $result['tid'], true);
                $link = route('biller.view_bill', [$result->id, 1, $valid_token, 0]);
                $short_url = ConfigMeta::where('feature_id', '=', 7)->first(array('feature_value', 'value2'));
                $data['URL'] = $link;
                if ($short_url['feature_value']) {
                    config([
                        'bitly.accesstoken' => $short_url['value2']
                    ]);
                    $data['URL'] = Bitly::getUrl($link);
                }
                $replaced_body = parse($template['body'], $data, true);
                $mailer = new \App\Repositories\Focus\general\RosesmsRepository();
                return $mailer->send_bill_sms($result->customer->phone, $replaced_body, false);
            }
        } else {
            echo json_encode(array('status' => 'Error', 'message' => trans('exceptions.backend.invoices.create_error')));
        }
    }

    public function pos_update(ManagePosRequest $request, PrinterController $printer)
    {
        //Input received from the request
        $invoice = $request->only(['customer_id', 'tid', 'refer', 'invoicedate', 'invoiceduedate', 'recur_after', 'sub', 'notes', 'subtotal', 'shipping', 'tax', 'discount', 'discount_rate', 'after_disc', 'currency', 'total', 'tax_format', 'discount_format', 'ship_tax', 'ship_tax_type', 'ship_rate', 'term_id', 'tax_id', 'p', 'id']);

        $invoice_items = $request->only(['product_id', 'product_name', 'code', 'product_qty', 'product_price', 'product_tax', 'product_discount', 'product_subtotal', 'product_subtotal', 'total_tax', 'total_discount', 'product_description', 'unit', 'serial', 'unit_m']);

        $invoice_payment = $request->only(['p_amount', 'p_method', 'p_account', 'b_change']);
        $data2 = $request->only(['custom_field']);
        $data2['ins'] = auth()->user()->ins;
        //dd($invoice_items);
        $invoice['ins'] = auth()->user()->ins;
        $invoice['user_id'] = auth()->user()->id;
        $invoice_items['ins'] = auth()->user()->ins;
        //Create the model using repository create method
        $invoice_ins = Invoice::find($invoice['id']);
        $result = $this->repository->update($invoice_ins, compact('invoice', 'invoice_items', 'data2'));
        if (isset($result['id'])) $pay = $this->repository->payment($result, $invoice_payment);
        //return with successfull message
        $valid_token = token_validator('', 'i' . $result['id'] . $result['tid'], true);
        $link = route('biller.print_bill', [$result['id'], 1, $valid_token, 1]);
        $link_download = route('biller.print_bill', [$result['id'], 1, $valid_token, 2]);
        $link_preview = route('biller.view_bill', [$result['id'], 1, $valid_token, 0]);
        $lk = '';
        $out = '';
        if (feature(19)->feature_value == 1) $out = $printer->thermal_print($result);
        if (isset($result['p'])) $lk .= '<a href="' . route('biller.projects.show', [$result['p']]) . '" class="btn btn-info btn-md"><span class="fa fa-repeat" aria-hidden="true"></span> ' . trans('invoices.return_project') . '  </a> ';
        if ($pay) echo json_encode(array('status' => 'Success', 'message' => trans('alerts.backend.invoices.created') . ' <a href="' . route('biller.invoices.show', [$result->id]) . '" class="btn btn-info btn-md"><span class="fa fa-eye" aria-hidden="true"></span> ' . trans('general.view') . '  </a> <a href="' . $link . '" class="btn btn-purple btn-md"><span class="fa fa-print" aria-hidden="true"></span> ' . trans('general.print') . '  </a> <a href="' . $link_download . '" class="btn btn-warning btn-md"><span class="fa fa-file-pdf-o" aria-hidden="true"></span> ' . trans('general.pdf') . '  </a> <a href="' . $link_preview . '" class="btn btn-purple btn-md"><span class="fa fa-globe" aria-hidden="true"></span> ' . trans('general.preview') . '  </a> <a href="' . route('biller.invoices.pos') . '" class="btn btn-blue-grey btn-md"><span class="fa fa-plus-circle" aria-hidden="true"></span> ' . trans('general.create') . '  </a> ' . $lk . ' &nbsp; &nbsp;<br>' . $out, 'd_id' => $result['id']));
    }

    public function draft_store(ManagePosRequest $request)
    {
        //Input received from the request
        $invoice = $request->only(['customer_id', 'tid', 'refer', 'invoicedate', 'invoiceduedate', 'recur_after', 'sub', 'notes', 'subtotal', 'shipping', 'tax', 'discount', 'discount_rate', 'after_disc', 'currency', 'total', 'tax_format', 'discount_format', 'ship_tax', 'ship_tax_type', 'ship_rate', 'term_id', 'tax_id', 'p']);

        $invoice_items = $request->only(['product_id', 'product_name', 'code', 'product_qty', 'product_price', 'product_tax', 'product_discount', 'product_subtotal', 'product_subtotal', 'total_tax', 'total_discount', 'product_description', 'unit', 'serial', 'unit_m']);

        $data2 = $request->only(['custom_field']);
        $data2['ins'] = auth()->user()->ins;
        //dd($invoice_items);
        $invoice['ins'] = auth()->user()->ins;
        $invoice['user_id'] = auth()->user()->id;
        $invoice_items['ins'] = auth()->user()->ins;
        //Create the model using repository create method
        $result = $this->repository->create_draft(compact('invoice', 'invoice_items', 'data2'));

        //return with successfull message
        $valid_token = token_validator('', 'i' . $result['id'] . $result['tid'], true);
        $link = route('biller.print_bill', [$result['id'], 1, $valid_token, 1]);
        $link_download = route('biller.print_bill', [$result['id'], 1, $valid_token, 2]);
        $link_preview = route('biller.view_bill', [$result['id'], 1, $valid_token, 0]);
        $lk = '';
        $out = '';

        echo json_encode(array('status' => 'Done', 'message' => trans('alerts.backend.invoices.draft_created')));
    }

    public function drafts_load(ManagePosRequest $request)
    {
        $drafts = Draft::where('user_id', '=', auth()->user()->id)->orderBy('id', 'desc')->take(20)->get();
        foreach ($drafts as $draft) {
            echo '<tr><td>' . $draft->tid . '#' . $draft->id . '<a href="' . route('biller.invoices.draft_view', [$draft->id]) . '"><i class="fa fa-eye" </a></td><td>' . dateTimeFormat($draft->created_at) . '</td><td>' . $draft->user->first_name . '</td></tr>';
        }
    }

    public function draft_view(ManagePosRequest $request)
    {
        $invoice = Draft::find($request->id);
        $customer = Customer::first();
        $accounts = Account::all();

        $input['sub'] = false;
        $last_invoice = Invoice::orderBy('id', 'desc')->where('i_class', '=', 1)->first();
        $invoice['tid'] = $last_invoice['tid'] + 1;
        $action = route('biller.invoices.pos_store');
        session(['d_id' => $invoice['id']]);
        return view('focus.invoices.pos.edit')->with(array('last_invoice' => $last_invoice, 'sub' => $input['sub'], 'p' => $request->p, 'accounts' => $accounts, 'customer' => $customer, 'invoices' => $invoice, 'action' => $action))->with(bill_helper(1, 2));
    }
}
