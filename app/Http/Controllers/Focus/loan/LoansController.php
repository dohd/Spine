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

namespace App\Http\Controllers\Focus\loan;

use App\Repositories\Focus\loan\LoanRepository;
use App\Http\Responses\ViewResponse;
use App\Http\Controllers\Controller;
use App\Http\Responses\RedirectResponse;
use App\Models\account\Account;
use App\Models\loan\Loan;
use App\Models\loan\Paidloan;
use Illuminate\Http\Request;

/**
 * CustomersController
 */
class LoansController extends Controller
{
    /**
     * variable to store the repository object
     * @var LoanRepository
     */
    protected $repository;

    /**
     * contructor to initialize repository object
     * @param LoanRepository $repository ;
     */
    public function __construct(LoanRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Display a listing of the resource.
     *
     * @param App\Http\Requests\Focus\customer\ManageCustomerRequest $request
     * @return \App\Http\Responses\ViewResponse
     */
    public function index()
    {
        return new ViewResponse('focus.loans.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @param CreateCustomerRequestNamespace $request
     * @return \App\Http\Responses\Focus\customer\CreateResponse
     */
    public function create()
    {
        // loan lender accounts and bank accounts
        $accounts = Account::whereHas('accountType', function ($q) {
            $q->whereIn('system', ['loan', 'bank']);
        })->get(['id', 'holder', 'account_type']);
        $last_tid = Loan::max('tid');

        return new ViewResponse('focus.loans.create', compact('last_tid', 'accounts'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param StoreCustomerRequestNamespace $request
     * @return \App\Http\Responses\RedirectResponse
     */
    public function store(Request $request)
    {
        // extract input fields
        $data = $request->only(['tid', 'bank_id', 'lender_id', 'amount', 'amount_pm', 'date', 'note', 'time_pm']);

        $data = $data + ['ins' => auth()->user()->ins, 'user_id' => auth()->user()->id];
            
        $this->repository->create($data);

        return new RedirectResponse(route('biller.loans.index'), ['flash_success' => 'Loan created successfully']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param DeleteCustomerRequestNamespace $request
     * @param App\Models\customer\Customer $customer
     * @return \App\Http\Responses\RedirectResponse
     */
    public function show(Loan $loan)
    {
        return new ViewResponse('focus.loans.view', compact('loan'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param App\Models\Loan $loan
     * @return \App\Http\Responses\RedirectResponse
     */
    public function destroy(Loan $loan)
    {
        $this->repository->delete($loan);
        
        return new RedirectResponse(route('biller.loans.index'), ['flash_success' => 'Loan deleted successfully']);
    }


    /**
     * Approve Loan
     */
    public function approve_loan(Loan $loan)
    {
        $this->repository->approve_loan($loan);

        return new RedirectResponse(route('biller.loans.index'), ['flash_success' => 'Loan approved successfully']);
    }

    /**
     * Form for paying loan
     */
    public function pay_loans()
    {
        $accounts = Account::whereHas('accountType', function($q) {
            $q->whereIn('system', ['bank', 'expense', 'other_current_liability']);
        })->where('system', null)
        ->get(['id', 'holder', 'account_type']);
        $last_tid = Paidloan::max('tid');

        return new ViewResponse('focus.loans.pay_loans', compact('last_tid', 'accounts'));
    }

    /**
     * Persist paid loan in storage
     */
    public function store_loans(Request $request)
    {
        // extract input fields
        $data = $request->only([
            'lender_id', 'bank_id', 'tid', 'date', 'payment_mode', 'amount', 'ref', 'interest_id', 'penalty_id'
        ]);
        $data_items = $request->only(['loan_id', 'paid', 'interest', 'penalty']);

        $data['ins'] = auth()->user()->ins;
        $data['user_id'] = auth()->user()->id;

        // modify and filter paid bill
        $data_items = modify_array($data_items);
        $data_items = array_filter($data_items, function ($item) { return $item['paid']; });

        $result = $this->repository->store_loans(compact('data', 'data_items'));

        return new RedirectResponse(route('biller.loans.index'), ['flash_success' => 'Loans payment successfully received']);
    }

    /**
     * Lenders for select dropdown search 
     */
    public function lenders()
    {
        // loan lender accounts
        $accounts = Account::whereHas('accountType', function ($q) {
            $q->where('system', 'loan');
        })->where('holder', 'LIKE', '%'. request('keyword') .'%')
        ->where('system', null)
        ->limit(6)->get(['id', 'holder', 'account_type']);

        return response()->json($accounts);
    }

    /**
     * Lender loans
     */
    public function lender_loans()
    {
        $accounts = Loan::where(['lender_id' => request('id'), 'is_approved' => 1])
            ->whereIn('status', ['pending', 'partial'])->get();

        return response()->json($accounts);
    }
}
