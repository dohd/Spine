<?php

namespace App\Http\Controllers\Focus\invoice_payment;

use App\Http\Controllers\Controller;
use App\Http\Responses\RedirectResponse;
use App\Http\Responses\ViewResponse;
use App\Models\account\Account;
use App\Models\invoice\InvoicePayment;
use App\Repositories\Focus\invoice_payment\InvoicePaymentRepository;
use Illuminate\Http\Request;

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
        return new ViewResponse('focus.invoicepayments.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $tid = InvoicePayment::where('ins', auth()->user()->ins)->max('tid');

        $accounts = Account::whereHas('accountType', function ($q) {
            $q->where('system', 'bank');
        })->get(['id', 'holder']);

        $unallocated_pmts = InvoicePayment::whereIn('payment_type', ['on_account', 'advance_payment'])
            ->whereColumn('amount', '!=', 'allocate_ttl')
            ->orderBy('date', 'asc')->get();

        return new ViewResponse('focus.invoicepayments.create_payment', compact('accounts', 'tid', 'unallocated_pmts'));
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
            'payment_mode', 'reference', 'payment_id', 'payment_type'
        ]);
        $data_items = $request->only(['invoice_id', 'paid']); 
        $data_items = modify_array($data_items);

        $data['ins'] = auth()->user()->ins;
        $data['user_id'] = auth()->user()->id;

        try {
            $result = $this->repository->create(compact('data', 'data_items'));
        } catch (\Throwable $th) {
            return errorHandler('Error Updating Payment', $th);
        }

        return new RedirectResponse(route('biller.invoices.index_payment'), ['flash_success' => 'Payment updated successfully']);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(InvoicePayment $invoice_payment)
    {
        return new ViewResponse('focus.invoicepayments.view_payment', compact('payment'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit(InvoicePayment $invoice_payment)
    {
        $accounts = Account::whereHas('accountType', function ($q) {
            $q->where('system', 'bank');
        })->get(['id', 'holder']);

        $unallocated_pmts = InvoicePayment::whereIn('payment_type', ['on_account', 'advance_payment'])
            ->whereColumn('amount', '!=', 'allocate_ttl')
            ->orderBy('date', 'asc')->get();

        return new ViewResponse('focus.invoicepayments.edit_payment', compact('payment', 'accounts', 'unallocated_pmts'));
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
            'payment_mode', 'reference', 'payment_id', 'payment_type'
        ]);
        $data_items = $request->only(['id', 'invoice_id', 'paid']); 

        $data['ins'] = auth()->user()->ins;
        $data['user_id'] = auth()->user()->id;
        $data_items = modify_array($data_items);

        try {
            $result = $this->repository->update($invoice_payment, compact('data', 'data_items'));
        } catch (\Throwable $th) { dd($th);
            return errorHandler('Error Updating Payment', $th);
        }

        return new RedirectResponse(route('biller.invoices.index_payment'), ['flash_success' => 'Payment updated successfully']);
    }


    /**
     * Remove resource from storage
     */
    public function destroy(InvoicePayment $invoice_payment)
    {
        try {
            $this->repository->delete($invoice_payment);
        } catch (\Throwable $th) {
            return errorHandler('Error Deleting Payment', $th);
        }

        return new RedirectResponse(route('biller.invoices.index_payment'), ['flash_success' => 'Payment deleted successfully']);
    }
}
