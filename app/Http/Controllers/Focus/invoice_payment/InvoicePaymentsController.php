<?php

namespace App\Http\Controllers\Focus\invoice_payment;

use App\Http\Controllers\Controller;
use App\Http\Responses\RedirectResponse;
use App\Http\Responses\ViewResponse;
use App\Models\account\Account;
use App\Models\customer\Customer;
use App\Models\invoice_payment\InvoicePayment;
use App\Repositories\Focus\invoice_payment\InvoicePaymentRepository;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class InvoicePaymentsController extends Controller
{
    /**
     * variable to store the repository object
     * @var InvoicePayment
     */
    protected $repository;

    /**
     * contructor to initialize repository object
     * @param InvoicePayment $repository ;
     */
    public function __construct(InvoicePaymentRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $customers = Customer::get(['id', 'company']);

        return new ViewResponse('focus.invoice_payments.index', compact('customers'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $tid = InvoicePayment::where('ins', auth()->user()->ins)->max('tid');
        $customers = Customer::get(['id', 'company']);

        $accounts = Account::whereHas('accountType', function ($q) {
            $q->where('system', 'bank');
        })->get(['id', 'holder']);

        $unallocated_pmts = InvoicePayment::whereIn('payment_type', ['on_account', 'advance_payment'])
            ->whereColumn('amount', '!=', 'allocate_ttl')
            ->orderBy('date', 'asc')->get();

        return new ViewResponse('focus.invoice_payments.create', compact('customers', 'accounts', 'tid', 'unallocated_pmts'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // extract request input
        $data = $request->only([
            'account_id', 'customer_id', 'date', 'tid', 'deposit', 'amount', 'allocate_ttl',
            'payment_mode', 'reference', 'payment_id', 'payment_type', 'rel_payment_id', 'note'
        ]);
        $data_items = $request->only(['invoice_id', 'paid']); 
        $data_items = modify_array($data_items);

        $data['ins'] = auth()->user()->ins;
        $data['user_id'] = auth()->user()->id;

        try {
            $result = $this->repository->create(compact('data', 'data_items'));
        } catch (\Throwable $th) {
            if ($th instanceof ValidationException) throw $th;
            return errorHandler('Error Creating Invoice Payment', $th);
        }

        return new RedirectResponse(route('biller.invoice_payments.index'), ['flash_success' => 'Invoice Payment updated successfully']);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(InvoicePayment $invoice_payment)
    {
        return new ViewResponse('focus.invoice_payments.view', compact('invoice_payment'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit(InvoicePayment $invoice_payment)
    {   
        $customers = Customer::get(['id', 'company']);
        $accounts = Account::whereHas('accountType', function ($q) {
            $q->where('system', 'bank');
        })->get(['id', 'holder']);

        $unallocated_pmts = InvoicePayment::whereIn('payment_type', ['on_account', 'advance_payment'])
            ->whereColumn('amount', '!=', 'allocate_ttl')
            ->orderBy('date', 'asc')->get();

        return new ViewResponse('focus.invoice_payments.edit', compact('invoice_payment', 'customers', 'accounts', 'unallocated_pmts'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, InvoicePayment $invoice_payment)
    {
        // extract request input
        $data = $request->only([
            'account_id', 'customer_id', 'date', 'tid', 'deposit', 'amount', 'allocate_ttl',
            'payment_mode', 'reference', 'payment_id', 'payment_type', 'rel_payment_id', 'note'
        ]);
        $data_items = $request->only(['id', 'invoice_id', 'paid']); 

        $data['ins'] = auth()->user()->ins;
        $data['user_id'] = auth()->user()->id;
        $data_items = modify_array($data_items);

        try {
            $result = $this->repository->update($invoice_payment, compact('data', 'data_items'));
        } catch (\Throwable $th) {
            if ($th instanceof ValidationException) throw $th;
            return errorHandler('Error Updating Invoice Payment', $th);
        }

        return new RedirectResponse(route('biller.invoice_payments.index'), ['flash_success' => 'Invoice Payment updated successfully']);
    }


    /**
     * Remove resource from storage
     */
    public function destroy(InvoicePayment $invoice_payment)
    {
        try {
            $this->repository->delete($invoice_payment);
        } catch (\Throwable $th) {
            if ($th instanceof ValidationException) throw $th;
            return errorHandler('Error Deleting Invoice Payment', $th);
        }

        return new RedirectResponse(route('biller.invoice_payments.index'), ['flash_success' => 'Invoice Payment deleted successfully']);
    }
}
