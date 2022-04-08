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

use App\Http\Controllers\Controller;
use Yajra\DataTables\Facades\DataTables;
use App\Repositories\Focus\transaction\TransactionRepository;
use App\Http\Requests\Focus\transaction\ManageTransactionRequest;

/**
 * Class TransactionsTableController.
 */
class TransactionsTableController extends Controller
{
    /**
     * variable to store the repository object
     * @var TransactionRepository
     */
    protected $transaction;

    /**
     * contructor to initialize repository object
     * @param TransactionRepository $transaction ;
     */
    public function __construct(TransactionRepository $transaction)
    {
        $this->transaction = $transaction;
    }

    /**
     * This method return the data of the model
     * @param ManageTransactionRequest $request
     *
     * @return mixed
     */
    public function __invoke(ManageTransactionRequest $request)
    {
        //
        $core = $this->transaction->getForDataTable();
        return Datatables::of($core)
            ->escapeColumns(['id'])
            ->addIndexColumn()
            ->addColumn('account_id', function ($tr) {
                return $tr->account->holder;
            })
            ->addColumn('supplier', function ($tr) {
                return $tr->bill ? $tr->bill->supplier->name : '';
            })
            ->addColumn('debit', function ($tr) {
                return numberFormat($tr->debit);
            })
            ->addColumn('credit', function ($tr) {
                return numberFormat($tr->credit);
            })
            ->addColumn('tr_date', function ($tr) {
                return dateFormat($tr->tr_date);
            })
            ->addColumn('actions', function ($tr) {
                return '<a href="' . route('biller.print_payslip', [$tr['id'], 1, 1]) . '" class="btn btn-blue round" data-toggle="tooltip" data-placement="top" title="View">
                    <i class="fa fa-print"></i> </a>' 
                    .$tr->action_buttons;
            })
            ->make(true);
    }
}
