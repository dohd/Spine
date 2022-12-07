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

namespace App\Http\Controllers\Focus\transaction;

use App\Models\account\Account;
use App\Models\customer\Customer;
use App\Models\hrm\Hrm;
use App\Models\supplier\Supplier;
use App\Models\transaction\Transaction;
use App\Http\Controllers\Controller;
use App\Http\Responses\ViewResponse;
use App\Http\Responses\Focus\transaction\CreateResponse;
use App\Repositories\Focus\transaction\TransactionRepository;
use App\Http\Requests\Focus\transaction\ManageTransactionRequest;

use App\Http\Requests\Focus\transaction\StoreTransactionRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * TransactionsController
 */
class TransactionsController extends Controller
{
    /**
     * variable to store the repository object
     * @var TransactionRepository
     */
    protected $repository;

    /**
     * contructor to initialize repository object
     * @param TransactionRepository $repository ;
     */
    public function __construct(TransactionRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Display a listing of the resource.
     *
     * @param App\Http\Requests\Focus\transaction\ManageTransactionRequest $request
     * @return \App\Http\Responses\ViewResponse
     */
    public function index(ManageTransactionRequest $request)
    {
        // extract request fields
        $rel_type = $request->rel_type;
        $rel_id = $request->rel_id;
        $is_tax = $request->system == 'tax';

        $input = compact('rel_id', 'rel_type');
        $account_section = $this->account_section($input['rel_id'], $input['rel_type']);

        return new ViewResponse('focus.transactions.index', compact('input', 'is_tax') + $account_section);
    }

    /**
     * Account Ledger section
     */
    public function account_section($rel_id, $rel_type)
    {
        $segment = (object) array();
        $words = array();
        switch ($rel_type) {
            case 1:
                $segment = Customer::find($rel_id);
                $words['name'] = trans('customers.title');
                $words['name_data'] = $segment->name;
                $words['url'] = '<a href="' . route('biller.customers.show', [$segment['id']]) . '">
                    <i class="fa fa-user"></i> ' . $segment['name'] . ' </a>';
                break;
            case 2:
                $segment = Hrm::find($rel_id);
                $words['name'] = trans('hrms.employee');
                $words['name_data'] = $segment->first_name . ' ' . $segment->last_name;
                $words['url'] = '<a href="' . route('biller.hrms.show', [$segment['id']]) . '">
                    <i class="fa fa-user"></i> ' . $words['name_data'] . ' </a>';
                break;
            case 3:
                $segment = Hrm::find($rel_id);
                $words['name'] = trans('hrms.employee');
                $words['name_data'] = $segment->first_name . ' ' . $segment->last_name;
                $words['url'] = '<a href="' . route('biller.hrms.show', [$segment['id']]) . '">
                    <i class="fa fa-user"></i> ' . $words['name_data'] . ' </a>';
                break;
            case 4:
                $segment = Supplier::find($rel_id);
                $words['name'] = trans('customers.title');
                $words['name_data'] = $segment->name;
                $words['url'] = '<a href="' . route('biller.customers.show', [$segment['id']]) . '">
                    <i class="fa fa-user"></i> ' . $segment['name'] . ' </a>';
                break;
            case 9:
                $segment = Account::find($rel_id);
                $words['name'] = trans('accounts.holder');
                $words['name_data'] = $segment->holder;
                break;
        }
        return compact('words', 'segment');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @param CreateTransactionRequestNamespace $request
     * @return \App\Http\Responses\Focus\transaction\CreateResponse
     */
    public function create(StoreTransactionRequest $request)
    {
        return new CreateResponse('focus.transactions.create');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param DeleteTransactionRequestNamespace $request
     * @param App\Models\transaction\Transaction $transaction
     * @return \App\Http\Responses\RedirectResponse
     */
    public function destroy(Transaction $transaction)
    {
        $result = $this->repository->delete($transaction);

        $msg = ['flash_success' => 'Transaction deleted successfully'];
        if (!$result) $msg = ['flash_error' => 'Reconciled transaction cannot be deleted'];

        return redirect()->back()->with($msg);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Transaction $transaction, ManageTransactionRequest $request)
    {
        return new ViewResponse('focus.transactions.view', compact('transaction'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param App\Models\Transaction $transaction,
     * @param EditProductcategoryRequestNamespace $request
     * @return \App\Http\Responses\Focus\productcategory\EditResponse
     */
    public function edit(Transaction $transaction)
    {
        // 
    }

    /**
     * Update the specified resource.
     * 
     * @param App\Models\Transaction $transaction
     * @param EditProductcategoryRequestNamespace $request
     * @return \App\Http\Responses\Focus\productcategory\EditResponse
     */
    public function update(Request $request, Transaction $transaction)
    {
        // extract input fields
        $input = $request->except('_token');
        $input['user_id'] = auth()->user()->id;
    
        //Update the model using repository update method
        $this->repository->update($transaction, $input);

        return redirect()->back()->with(['flash_success' => 'Transaction updated successfully']);
    }

    /**
     * Search transaction account
     */
    public function account_search(Request $request)
    {
        $q = $request->keyword;
        $accounts = Account::where('holder', 'LIKE', '%' . $q . '%')
            ->orWhere('number', 'LIKE', '%' . $q . '%')
            ->limit(6)->get(['id', 'holder']);

        return response()->json($accounts);
    }

    /**
     * Payer search
     */
    public function payer_search(ManageTransactionRequest $request)
    {
        $q = $request->post('keyword');
        $c = $request->post('payer_type');
        $t = 0;
        switch ($c) {
            case 'customer':
                $user = \App\Models\customer\Customer::with('primary_group')->where('name', 'LIKE', '%' . $q . '%')->where('active', '=', 1)->orWhere('email', 'LIKE', '%' . $q . '')->limit(6)->get(array('id', 'taxid', 'name', 'phone', 'address', 'city', 'email'));
                $t = 1;
                break;
            case 'supplier':
                $user = \App\Models\supplier\Supplier::where('name', 'LIKE', '%' . $q . '%')->where('active', '=', 1)->orWhere('email', 'LIKE', '%' . $q . '')->limit(6)->get(array('id', 'name', 'taxid', 'phone', 'address', 'city', 'email'));
                $t = 2;
                break;
            case 'employee':
                $user = \App\Models\hrm\Hrm::where('first_name', 'LIKE', '%' . $q . '%')->where('status', '=', 1)->orWhere('email', 'LIKE', '%' . $q . '')->select(DB::raw("TRIM(CONCAT(first_name,' - ',last_name)) AS name,taxid,id,email"))->limit(6)->get();
                $t = 3;
                break;
            default:
                $user = false;
        }

        if (!$q) return false;
        if (count($user) > 0) return view('focus.transactions.partials.search')->with(compact('user', 't'));
    }
}
