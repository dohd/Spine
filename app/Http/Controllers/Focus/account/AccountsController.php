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

namespace App\Http\Controllers\Focus\account;

use App\Models\account\Account;
use App\Models\Company\ConfigMeta;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Responses\RedirectResponse;
use App\Http\Responses\ViewResponse;
use App\Http\Responses\Focus\account\CreateResponse;
use App\Http\Responses\Focus\account\EditResponse;
use App\Repositories\Focus\account\AccountRepository;
use App\Http\Requests\Focus\account\ManageAccountRequest;
use App\Http\Requests\Focus\account\StoreAccountRequest;
use Illuminate\Support\Facades\Response;
use Illuminate\Validation\ValidationException;

/**
 * AccountsController
 */
class AccountsController extends Controller
{
    /**
     * variable to store the repository object
     * @var AccountRepository
     */
    protected $repository;

    /**
     * contructor to initialize repository object
     * @param AccountRepository $repository ;
     */
    public function __construct(AccountRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Display a listing of the resource.
     *
     * @param App\Http\Requests\Focus\account\ManageAccountRequest $request
     * @return \App\Http\Responses\ViewResponse
     */
    public function index(ManageAccountRequest $request)
    {
        return new ViewResponse('focus.accounts.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @param CreateAccountRequestNamespace $request
     * @return \App\Http\Responses\Focus\account\CreateResponse
     */
    public function create(StoreAccountRequest $request)
    {
        return new CreateResponse('focus.accounts.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param StoreAccountRequestNamespace $request
     * @return \App\Http\Responses\RedirectResponse
     */
    public function store(StoreAccountRequest $request)
    {
        $request->validate([
            'number' => 'required',
            'holder' => 'required',
            'is_parent'=> 'required',
            'is_manual_journal'=> 'required',
            'account_type' => 'required',
        ]);
        // constraint for duplicate accounts of specific account-type e.g receivable and payable
        if (!$request->is_multiple) 
            throw ValidationException::withMessages(['account_type' => 'Duplicate account type is not allowed']);

        // extract request input
        $input = $request->except(['_token']);
        $input['ins'] =  auth()->user()->ins;

        $this->repository->create($input);

        return new RedirectResponse(route('biller.accounts.index'), ['flash_success' => trans('alerts.backend.accounts.created')]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param App\Models\account\Account $account
     * @param EditAccountRequestNamespace $request
     * @return \App\Http\Responses\Focus\account\EditResponse
     */
    public function edit(Account $account)
    {
        return new EditResponse($account);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param UpdateAccountRequestNamespace $request
     * @param App\Models\account\Account $account
     * @return \App\Http\Responses\RedirectResponse
     */
    public function update(StoreAccountRequest $request, Account $account)
    {
        $request->validate([
            'number' => 'required',
            'holder' => 'required'
        ]);
        $input = $request->except(['_token', 'ins']);

        $this->repository->update($account, $input);

        return new RedirectResponse(route('biller.accounts.index'), ['flash_success' => trans('alerts.backend.accounts.updated')]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param DeleteAccountRequestNamespace $request
     * @param App\Models\account\Account $account
     * @return \App\Http\Responses\RedirectResponse
     */
    public function destroy(Account $account)
    {
        $this->repository->delete($account);

        return new RedirectResponse(route('biller.accounts.index'), ['flash_success' => trans('alerts.backend.accounts.deleted')]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param DeleteAccountRequestNamespace $request
     * @param App\Models\account\Account $account
     * @return \App\Http\Responses\RedirectResponse
     */
    public function show(Account $account, ManageAccountRequest $request)
    {
        $params =  ['rel_type' => 9, 'rel_id' => $account->id, 'system' => $account->system];
        return new RedirectResponse(route('biller.transactions.index', $params), '');
    }

    /**
     * Search Expense accounts 
     */
    public function account_search(Request $request)
    {
        if (!access()->allow('product_search')) return false;

        $q = $request->post('keyword');

        $accounts = Account::where('holder', 'LIKE', '%' . $q . '%')
            ->where('account_type', 'Expense')
            ->orWhere('number', 'LIKE', '%' . $q . '%')
            ->limit(6)->get(['id', 'holder AS name', 'number']);

        return response()->json($accounts);
    }


    public function profit_and_loss(Request $request)
    {
        $dates = $request->only('start_date', 'end_date');
        $dates = array_map(function ($v) { return date_for_database($v); }, $dates);

        $q = Account::query();
        if ($dates) {
            $q->whereHas('transactions', function ($q) use($dates) {
                $q->whereBetween('tr_date', $dates);
            });
        } else $q->whereHas('transactions');

        $accounts = $q->get();
        if ($request->type == 'p')             
            return $this->print_document('profit_and_loss', $accounts, $dates, 0);

        $bg_styles = [
            'bg-gradient-x-info', 'bg-gradient-x-purple', 'bg-gradient-x-grey-blue', 'bg-gradient-x-danger',
        ];
            
        return new ViewResponse('focus.accounts.profit_&_loss', compact('accounts', 'bg_styles', 'dates'));
    }

    public function balance_sheet(Request $request)
    {
        $date = date_for_database(request('end_date'));

        $q = Account::query();
        $q1 = clone $q;
        if (request('end_date')) {
            $q->whereHas('transactions', function ($q) use($date) {
                $q->where('tr_date', '<=', $date);
            })->whereIn('account_type', ['Asset', 'Equity', 'Liability']);
            $q1->whereHas('transactions', function ($q) use($date) {
                $q->where('tr_date', '<=', $date);
            })->whereIn('account_type', ['Income', 'Expense']);
        } else {
            $q->whereHas('transactions')->whereIn('account_type', ['Asset', 'Equity', 'Liability']);
            $q1->whereHas('transactions')->whereIn('account_type', ['Income', 'Expense']);
        }

        // compute profit and loss
        $net_profit = 0;
        $net_accounts = $q1->get();
        foreach ($net_accounts as $account) {
            $is_revenue = $account->account_type == 'Income';
            $is_cog = $account->system == 'cog';
            $is_dir_expense = $account->account_type == 'Expense' && $account->system != 'cog';
            $debit = $account->transactions->sum('debit');
            $credit = $account->transactions->sum('credit');
            if ($is_revenue) $net_profit += $credit;
            elseif ($is_cog) $net_profit -= $debit;
            elseif ($is_dir_expense) $net_profit -= $debit;
        }

        $accounts = $q->get();
        if ($request->type == 'p')             
            return $this->print_document('balance_sheet', $accounts, array(0, $date), $net_profit);

        $bg_styles = [
            'bg-gradient-x-info', 'bg-gradient-x-purple', 'bg-gradient-x-grey-blue', 'bg-gradient-x-danger', 
        ];
    
        return new ViewResponse('focus.accounts.balance_sheet', compact('accounts', 'bg_styles', 'net_profit', 'date'));
    }


    public function trial_balance(Request $request)
    {
        $q = Account::query();
        $date = date_for_database(request('end_date'));
        if (request('end_date')) {
            $q->whereHas('transactions', function($q) use($date) {
                $q->where('tr_date', '<=', $date);
            });
        } else $q->whereHas('transactions');
        
        $accounts = $q->orderBy('number', 'asc')->get();
        if ($request->type == 'p')
            return $this->print_document('trial_balance', $accounts, [0, $date], 0);

        return new ViewResponse('focus.accounts.trial_balance', compact('accounts', 'date'));
    }

    /**
     * Search next account number
     */
    public function search_next_account_no(Request $request)
    {
        $account_type = $request->account_type;

        $account = Account::where('account_type', $account_type)->max('number');
        $netx_account = accounts_numbering($account_type);
        if ($account > 0) $netx_account = $account + 1;
            
        return response()->json(['account_number' => $netx_account]);
    }

    /**
     * Print docume
     */
    public function print_document(string $name, $accounts, array $dates, int $net_profit)
    {
        $account_types = ['Assets', 'Equity', 'Expenses', 'Liabilities', 'Income'];
        $params = compact('accounts', 'account_types', 'dates', 'net_profit');
        $html = view('focus.accounts.print_' . $name, $params)->render();
        $pdf = new \Mpdf\Mpdf(config('pdf'));
        $pdf->WriteHTML($html);
        $headers = array(
            "Content-type" => "application/pdf",
            "Pragma" => "no-cache",
            "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
            "Expires" => "0"
        );
        return Response::stream($pdf->Output($name . '.pdf', 'I'), 200, $headers);
    }
}
