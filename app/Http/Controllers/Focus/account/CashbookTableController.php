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
class CashbookTableController extends Controller
{
    /**
     * variable to store the repository object
     * @var AccountRepository
     */
    protected $repository;

    /**
     * contructor to initialize repository object
     * @param AccountRepository $repository;
     */
    public function __construct(AccountRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * This method return the data of the model
     * @param ManageAccountRequest $request
     *
     * @return mixed
     */
    public function __invoke(ManageAccountRequest $request)
    {
        // $core = $this->transaction();
        $core = AccountsController::cashbook_transactions();
        
        $sum_debit = $core->sum('debit');
        $sum_credit = $core->sum('credit');
        $balance = $sum_debit - $sum_credit;
        $aggregate = compact('sum_debit', 'sum_credit', 'balance');

        return Datatables::of($core)
            ->escapeColumns(['id'])
            ->addIndexColumn()
            ->addColumn('tr_date', function ($transaction) {
                return dateFormat($transaction->tr_date);
            })
            ->addColumn('note', function ($transaction) {
                return $transaction->note;
            })
            ->addColumn('tr_type', function ($transaction) {
                if ($transaction->debit > 0) 
                    return 'Receipt';
                return 'Payment';
            })
            ->addColumn('tid', function ($transaction) {
                $link = '';
                if ($transaction->debit > 0) {
                    $invoice_pmt = $transaction->invoice_payment;
                    if ($invoice_pmt && $invoice_pmt->customer_id) {
                        $link .= '<a href="'. route('biller.invoices.show_payment', $invoice_pmt) .'">'. $invoice_pmt->tid .'</a>';
                    } else {
                        $link .= '<a href="'. route('biller.banktransfers.show', $transaction->id) .'">'. $transaction->tid .'</a>';
                    }
                } else {
                    $bill_pmt = $transaction->bill_payment;
                    if ($bill_pmt && $bill_pmt->supplier_id) {
                        $link .= '<a href="'. route('biller.billpayments.show', $bill_pmt) .'">'. $bill_pmt->tid .'</a>';
                    } 
                }

                return $link;
            })
            ->addColumn('account', function ($transaction) {
                if ($transaction->account) return $transaction->account->holder;
            })
            ->addColumn('debit', function ($transaction) {
                if ($transaction->debit > 0)
                    return numberFormat($transaction->debit);
            })
            ->addColumn('credit', function ($transaction) {
                if ($transaction->credit > 0)
                    return numberFormat($transaction->credit);
            })
            ->addColumn('aggregate', function ($transaction) use($aggregate) {
                foreach ($aggregate as $key => $val) {
                    $aggregate[$key] = numberFormat($val);
                }
                return $aggregate;
            })
            ->make(true);
    }
}
