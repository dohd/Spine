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

use App\Http\Controllers\Controller;
use Yajra\DataTables\Facades\DataTables;
use App\Repositories\Focus\account\AccountRepository;
use App\Http\Requests\Focus\account\ManageAccountRequest;

/**
 * Class AccountsTableController.
 */
class AccountsTableController extends Controller
{
    /**
     * variable to store the repository object
     * @var AccountRepository
     */
    protected $account;

    /**
     * contructor to initialize repository object
     * @param AccountRepository $account ;
     */
    public function __construct(AccountRepository $account)
    {
        $this->account = $account;
    }

    /**
     * This method return the data of the model
     * @param ManageAccountRequest $request
     *
     * @return mixed
     */
    public function __invoke(ManageAccountRequest $request)
    {
        $core = $this->account->getForDataTable();

        return Datatables::of($core)
            ->escapeColumns(['id', 'number', 'holder'])
            ->addIndexColumn()
            ->addColumn('debit', function ($account) {
                return numberFormat($account->debit);
            })
            ->addColumn('credit', function ($account) {
                return numberFormat($account->credit);
            })
            ->addColumn('balance', function ($account) {
                $balance = $account->credit - $account->debit;
                if (in_array($account->account_type, ['Asset', 'Expense']))
                    $balance = $account->debit - $account->credit;

                return numberFormat($balance);
            })
            ->addColumn('account_type', function ($account) {
                return  $account->account_type;
            })
            ->addColumn('system_type', function ($account) {
                return $account->system? 'default' : 'custom';
            })
            ->addColumn('actions', function ($account) {
                return $account->action_buttons;
            })
            ->make(true);
    }
}
