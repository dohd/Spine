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
use App\Models\lead\Lead;
use App\Models\loan\Loan;
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
        $last_loan = Loan::orderBy('id', 'desc')->first(['tid']);
        $accounts = Account::whereIn('account_type_id', [2, 7])->get(['id', 'holder', 'account_type_id']);

        return new ViewResponse('focus.loans.create', compact('last_loan', 'accounts'));
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
        $data = $request->only([
            'tid', 'bank_id', 'lender_id', 'amount', 'amount_pm', 'date', 'note',
            'time_pm'
        ]);

        $data = $data + [
            'ins' => auth()->user()->ins,
            'user_id' => auth()->user()->id
        ];

        $result = $this->repository->create($data);

        return new RedirectResponse(route('biller.loans.index'), ['flash_success' => 'Loan created successfully']);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param App\Models\customer\Customer $customer
     * @param EditCustomerRequestNamespace $request
     * @return \App\Http\Responses\Focus\customer\EditResponse
     */
    public function edit($request)
    {
        //return new EditResponse($customer);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param UpdateCustomerRequestNamespace $request
     * @param App\Models\customer\Customer $customer
     * @return \App\Http\Responses\RedirectResponse
     */
    public function update($customer)
    {
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param DeleteCustomerRequestNamespace $request
     * @param App\Models\customer\Customer $customer
     * @return \App\Http\Responses\RedirectResponse
     */
    public function destroy($request)
    {
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
     * Approve Loan
     */
    public function approve_loan(Loan $loan)
    {
        $loan->update(['is_approved' => 1]);
        // accounts
        $this->repository->post_transaction($loan);

        return new RedirectResponse(route('biller.loans.index'), ['flash_success' => 'Loan approved successfully']);
    }
}
