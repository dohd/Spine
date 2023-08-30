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
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Responses\RedirectResponse;
use App\Http\Responses\ViewResponse;
use App\Http\Responses\Focus\account\CreateResponse;
use App\Http\Responses\Focus\account\EditResponse;
use App\Repositories\Focus\account\AccountRepository;
use App\Http\Requests\Focus\account\ManageAccountRequest;
use App\Http\Requests\Focus\account\StoreAccountRequest;
use App\Models\customer\Customer;
use App\Models\transaction\Transaction;
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
        if (!request('is_multiple')) throw ValidationException::withMessages([
            'account_type' => 'Duplicate account type is not allowed'
        ]);
            

        // extract request input
        $input = $request->except(['_token']);
        $input['ins'] =  auth()->user()->ins;

        try {
            $this->repository->create($input);
        } catch (\Throwable $th) {
            if ($th instanceof ValidationException) throw $th;
            return errorHandler('Error Creating Account', $th);
        }

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

        try {
            $this->repository->update($account, $input);
        } catch (\Throwable $th) {
            if ($th instanceof ValidationException) throw $th;
            return errorHandler('Error Updating Account', $th);
        }

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
        try {
            $this->repository->delete($account);
        } catch (\Throwable $th) {
            if ($th instanceof ValidationException) throw $th;
            return errorHandler('Error Deleting Account', $th);
        }

        return new RedirectResponse(route('biller.accounts.index'), ['flash_success' => trans('alerts.backend.accounts.deleted')]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param DeleteAccountRequestNamespace $request
     * @param App\Models\account\Account $account
     * @return \App\Http\Responses\RedirectResponse
     */
    public function show(Account $account)
    {
        $params =  ['rel_type' => 9, 'rel_id' => $account->id, 'system' => $account->system];

        return new RedirectResponse(route('biller.transactions.index', $params), []);
    }

    /**
     * Search next account number
     */
    public function search_next_account_no(Request $request)
    {
        $account_type = $request->account_type;
        $number = Account::where('account_type', $account_type)->max('number');

        $series = accounts_numbering($account_type);
        if ($number) $series = $number + 1;
            
        return response()->json(['account_number' => $series]);
    }    

    /**
     * Search Expense accounts 
     */
    public function account_search(Request $request)
    {
        if (!access()->allow('product_search')) return false;

        $k = $request->keyword;
        
        if ($request->type == 'Expense') {
            $accounts = Account::where('account_type', 'Expense')
            ->where(function ($q) use($k) {
                $q->where('holder', 'LIKE', '%' . $k . '%')->orWhere('number', 'LIKE', '%' . $k . '%');
            })->limit(6)->get(['id', 'holder AS name', 'number']);

            return response()->json($accounts);
        }

        $accounts = Account::where('holder', 'LIKE', '%' . $k . '%')
            ->orWhere('number', 'LIKE', '%' . $k . '%')
            ->limit(6)->get(['id', 'holder AS name', 'number']);

        return response()->json($accounts);
    }

    /**
     * Profit and Loss (Income)
     */
    public function profit_and_loss(Request $request)
    {
        $dates = $request->only('start_date', 'end_date');
        $dates = array_map(function ($v) { 
            return date_for_database($v); 
        }, $dates);

        $q = Account::whereHas('transactions', function ($q) use($dates) {
                $q->when($dates, function ($q) use($dates) {
                    $q->whereBetween('tr_date', $dates);
                });
            });

        $accounts = $q->get();

        if ($request->type == 'p') {
            return $this->print_document('profit_and_loss', $accounts, $dates, 0);
        } 

        $bg_styles = [
            'bg-gradient-x-info', 'bg-gradient-x-purple', 'bg-gradient-x-grey-blue', 'bg-gradient-x-danger',
        ];
            
        return new ViewResponse('focus.accounts.profit_&_loss', compact('accounts', 'bg_styles', 'dates'));
    }

    /**
     * Balance Sheet
     */
    public function balance_sheet(Request $request)
    {
        $date = date_for_database(request('end_date'));

        $bal_sheet_q = Account::query();
        $profit_loss_q = Account::query();
        if (request('end_date')) {
            // balance sheet accounts
            $bal_sheet_q->whereIn('account_type', ['Asset', 'Equity', 'Liability'])
                ->whereHas('transactions', function ($q) use($date) {
                    $q->whereDate('tr_date', '<=', $date);
                });
            // profit & loss accounts
            $profit_loss_q->whereIn('account_type', ['Income', 'Expense'])
                ->whereHas('transactions', function ($q) use($date) {
                    $q->where('tr_date', '<=', $date);
                });
        } else {
            // balance sheet accounts
            $bal_sheet_q->whereHas('transactions')->whereIn('account_type', ['Asset', 'Equity', 'Liability']);
            // profit & loss accounts
            $profit_loss_q->whereHas('transactions')->whereIn('account_type', ['Income', 'Expense']);
        }

        // compute profit and loss
        $net_profit = 0;
        $net_accounts = $profit_loss_q->get();
        foreach ($net_accounts as $account) {
            $debit = $account->transactions()->sum('debit');
            $credit = $account->transactions()->sum('credit');
            $account_type = $account->account_type;
            if ($account_type == 'Income') {
                $credit_balance = round($credit - $debit, 2);
                $net_profit += $credit_balance;
            } elseif ($account_type == 'Expense') {
                $debit_balance = round($debit - $credit, 2);
                $net_profit -= $debit_balance;
            }
        }

        // fetch balance sheet accounts
        $accounts = $bal_sheet_q->get();
        $bg_styles = ['bg-gradient-x-info', 'bg-gradient-x-purple', 'bg-gradient-x-grey-blue', 'bg-gradient-x-danger'];

        // print balance_sheet
        if ($request->type == 'p') return $this->print_document('balance_sheet', $accounts, array(0, $date), $net_profit);       
            
        return new ViewResponse('focus.accounts.balance_sheet', compact('accounts', 'bg_styles', 'net_profit', 'date'));
    }

    /**
     * Trial Balance
     */
    public function trial_balance(Request $request)
    {   
        $end_date = $request->end_date? date_for_database($request->end_date) : '';
        $q = Account::whereHas('transactions', function ($q) use($end_date) {
            $q->when($end_date, function ($q) use($end_date) {
                $q->whereDate('tr_date', '<=', $end_date);
            });
        });
    
        $accounts = $q->orderBy('number', 'asc')->get();
        $date = date_for_database($end_date);
        if ($request->type == 'p') 
            return $this->print_document('trial_balance', $accounts, [0, $date], 0);
        
        return new ViewResponse('focus.accounts.trial_balance', compact('accounts', 'date'));
    }

    /**
     * Print document
     */
    public function print_document(string $name, $accounts, array $dates, float $net_profit)
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

    /**
     * Project Gross Profit Index
     */
    public function project_gross_profit()
    {
        $customers = Customer::whereHas('projects')->get(['id', 'company']);

        return new ViewResponse('focus.accounts.project_gross_profit', compact('customers'));
    }

    /**
     * Cashbook Index
     */
    public function cashbook()
    {
        $accounts = Account::whereHas('accountType', fn($q) => $q->where('system', 'bank'))
            ->where('account_type', 'Asset')->get(['id', 'holder']);

        return new ViewResponse('focus.accounts.cashbook', compact('accounts'));
    }
    // cashbook transasctions
    static function cashbook_transactions()
    {
        $q = Transaction::where(function($q) {
            $q->where('tr_type', 'pmt')->orWhere(fn($q) => $q->where('tr_type', 'xfer')->where('debit', '>', 0));
        })
        ->whereHas('account', function ($q) {
            $q->where('account_type', 'Asset')->whereHas('accountType', fn($q) => $q->where('system', 'bank'));
            $q->when(request('account_id'), fn($q) => $q->where('accounts.id', request('account_id')));
        })
        ->when(request('tr_type') == 'receipt', fn($q) => $q->where('debit', '>', 0))
        ->when(request('tr_type') == 'payment', fn($q) => $q->where('credit', '>', 0))
        ->when(request('start_date') && request('end_date'), function ($q) {
            $q->whereBetween('tr_date', [
                date_for_database(request('start_date')),
                date_for_database(request('end_date')),
            ]);
        });

        return $q->get();
    }
}
