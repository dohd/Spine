<?php

namespace App\Http\Controllers\Focus\billpayment;

use App\Http\Controllers\Controller;
use App\Http\Responses\RedirectResponse;
use App\Models\Access\User\User;
use App\Models\account\Account;
use App\Models\billpayment\Billpayment;
use App\Models\supplier\Supplier;
use App\Models\utility_bill\UtilityBill;
use App\Repositories\Focus\billpayment\BillPaymentRepository;
use DirectoryIterator;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class BillPaymentController extends Controller
{
    /**
     * Store repository object
     * @var \App\Repositories\Focus\billpayment\BillPaymentRepository
     */
    public $repository;

    public function __construct(BillPaymentRepository $repository)
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
        $suppliers = Supplier::get(['id', 'name']);
        return view('focus.billpayments.index', compact('suppliers'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        $tid = Billpayment::where('ins', auth()->user()->ins)->max('tid');
        $accounts = Account::whereNull('system')
            ->whereHas('accountType', fn($q) =>  $q->where('system', 'bank'))
            ->get(['id', 'holder']);
        $suppliers = Supplier::get(['id', 'name']);
        $employees = User::get();

        $direct_bill = [];
        $params = $request->only(['src_id', 'src_type']);
        if (count($params) == 2) {
            $bill = UtilityBill::where([
                'ref_id' => $params['src_id'],
                'document_type' => $params['src_type'],
                'status' => 'due'
            ])->first();
            if ($params['src_type'] == 'direct_purchase' && !$bill) {
                return redirect(route('biller.purchases.index'))->with([
                    'flash_error' => 'Bill Unavailable For Direct Payment.'
                ]);
            } else {
                $direct_bill = [
                    'tid' => $bill->tid,
                    'supplier_id' => $bill->supplier_id,
                    'amount' => $bill->total,
                ];
            }
        }

        $unallocated_pmts = Billpayment::whereIn('payment_type', ['on_account', 'advance_payment'])
            ->whereColumn('amount', '!=', 'allocate_ttl')
            ->orderBy('date', 'asc')->get();

        return view('focus.billpayments.create', compact('tid', 'accounts', 'suppliers', 'employees', 'direct_bill', 'unallocated_pmts'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        try {
            $this->repository->create($request->except('_token', 'balance', 'unallocate_ttl'));
        } catch (\Throwable $th) { 
            if ($th instanceof ValidationException) throw $th;
            return errorHandler('Error Creating Bill Payment!', $th);
        }

        return new RedirectResponse(route('biller.billpayments.index'), ['flash_success' => 'Bill Payment Created Successfully']);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\billpayment\Billpayment $billpayment
     * @return \Illuminate\Http\Response
     */
    public function edit(Billpayment $billpayment)
    {
        $suppliers = Supplier::get(['id', 'name']);
        $employees = User::get();
        $accounts = Account::whereNull('system')
            ->whereHas('accountType', fn($q) => $q->where('system', 'bank'))
            ->get(['id', 'holder']);

        $unallocated_pmts = Billpayment::where('payment_type', '!=', 'per_invoice')
            ->whereColumn('amount', '!=', 'allocate_ttl')
            ->orderBy('date', 'asc')->get();

        $is_allocated_pmt = false;
        $has_allocations = Billpayment::where('rel_payment_id', $billpayment->id)->exists();
        if ($billpayment->payment_type != 'per_invoice' && $has_allocations) $is_allocated_pmt = true;
            
        $is_next_allocation = false;
        $allocation_count = Billpayment::where('rel_payment_id', $billpayment->id)->count();
        if ($allocation_count > 1) $is_next_allocation = true;
        
        return view('focus.billpayments.edit', 
            compact('billpayment', 'accounts', 'suppliers', 'employees', 'unallocated_pmts', 'is_allocated_pmt', 'is_next_allocation')
        );
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\billpayment\Billpayment $billpayment
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Billpayment $billpayment)
    {
        try {
            $this->repository->update($billpayment, $request->except('_token', 'balance', 'unallocate_ttl'));
        } catch (\Throwable $th) {
            if ($th instanceof ValidationException) throw $th;
            return errorHandler('Error Updating Bill Payment!', $th);
        }

        return new RedirectResponse(route('biller.billpayments.index'), ['flash_success' => 'Bill Payment Updated Successfully']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\billpayment\Billpayment $billpayment
     * @return \Illuminate\Http\Response
     */
    public function destroy(Billpayment $billpayment)
    {
        try {
            $this->repository->delete($billpayment);
        } catch (\Throwable $th) {
            if ($th instanceof ValidationException) throw $th;
            return errorHandler('Error Deleting Bill Payment!', $th);
        }

        return new RedirectResponse(route('biller.billpayments.index'), ['flash_success' => 'Bill Payment Deleted Successfully']);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\billpayment\Billpayment $billpayment
     * @return \Illuminate\Http\Response
     */
    public function show(Billpayment $billpayment)
    {
        return view('focus.billpayments.view', compact('billpayment'));
    }
}
