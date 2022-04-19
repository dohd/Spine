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

use App\Http\Controllers\Controller;
use App\Repositories\Focus\loan\LoanRepository;
use Yajra\DataTables\Facades\DataTables;

/**
 * Class BanksTableController.
 */
class LoansTableController extends Controller
{
    /**
     * variable to store the repository object
     * @var LoanRepository
     */
    protected $loan;

    /**
     * contructor to initialize repository object
     * @param LoanRepository $loan ;
     */
    public function __construct(LoanRepository $loan)
    {
        $this->loan = $loan;
    }

    /**
     * This method return the data of the model
     *
     * @return mixed
     */
    public function __invoke()
    {
        $core = $this->loan->getForDataTable();

        return Datatables::of($core)
            ->escapeColumns(['id'])
            ->addIndexColumn()
            ->addColumn('lender', function ($loan) {
                return $loan->lender->holder;
            })
            ->addColumn('date', function ($loan) {
                return dateFormat($loan->date);
            })
            ->addColumn('amount', function ($loan) {
                return number_format($loan->amount, 2);
            })
            ->addColumn('amountpaid', function ($loan) {
                return number_format($loan->amountpaid, 2);
            })
            ->addColumn('is_approved', function ($loan) {
                return $loan->is_approved ? 'Approved' : 'Pending';
            })
            ->addColumn('actions', function ($loan) {
                return '<a href="' . route('biller.loans.show', $loan) . '" class="btn btn-primary round"><i class="fa fa-eye"></i></a> ' 
                    .' <a href="' . route('biller.loans.approve_loan', $loan) . '" type="button" class="btn btn-success round" data-toggle="tooltip" title="Approve" data-placement="top">
                        <i class="fa fa-check"></i></a>';
            })
            ->make(true);
    }
}