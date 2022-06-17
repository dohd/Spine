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

    public function tax_transaction($col='', $tr)
    {
        if (request('system') != 'tax') return;
        switch ($col) {
            case 'reference':
                if ($tr->invoice) 
                    return $tr->invoice->customer->taxid . ' : ' . $tr->invoice->customer->company;
                if ($tr->bill)
                    return $tr->bill->supplier->taxid . ' : ' . $tr->bill->supplier->company;
            case 'tr_type':
                if ($tr->invoice) return 'Sale';
                if ($tr->bill) return 'Purchase';
            case 'vat_rate':
                if ($tr->invoice) return $tr->invoice->tax_id;
                if ($tr->bill) return $tr->bill->tax;
            case 'vat_amount':
                if ($tr->invoice) return numberFormat($tr->invoice->tax);
                if ($tr->bill) return numberFormat($tr->bill->grandtax);
        }
    }

    /**
     * This method return the data of the model
     * @param ManageTransactionRequest $request
     *
     * @return mixed
     */
    public function __invoke(ManageTransactionRequest $request)
    {
        $core = $this->transaction->getForDataTable();

        return Datatables::of($core)
            ->escapeColumns(['id'])
            ->addIndexColumn()
            ->addColumn('tid', function ($tr) {
                return 'Tr-' . $tr->tid;                
            })
            ->addColumn('tr_type', function ($tr) {
                $result = $this->tax_transaction('tr_type', $tr);
                if ($result) return $result;
                
                return $tr->tr_type;
            })
            ->addColumn('reference', function ($tr) {
                $result = $this->tax_transaction('reference', $tr);
                if ($result) return $result;
                if ($tr->account) return $tr->account->holder . ' - ' . $tr->user_type;
            })
            ->addColumn('vat_rate', function ($tr) {
                return $this->tax_transaction('vat_rate', $tr);                
            })
            ->addColumn('vat_amount', function ($tr) {
                return $this->tax_transaction('vat_amount', $tr);
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
            ->addColumn('created_at', function ($tr) {
                return $tr->created_at->format('d-m-Y');
            })
            ->addColumn('actions', function ($tr) {
                return '<a href="' . route('biller.print_payslip', [$tr['id'], 1, 1]) . '" class="btn btn-blue round" data-toggle="tooltip" data-placement="top" title="View">
                    <i class="fa fa-print"></i> </a>'
                    . $tr->action_buttons;
            })
            ->make(true);
    }
}