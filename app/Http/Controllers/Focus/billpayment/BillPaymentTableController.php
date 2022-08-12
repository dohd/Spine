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
namespace App\Http\Controllers\Focus\billpayment;

use App\Http\Controllers\Controller;
use App\Repositories\Focus\billpayment\BillPaymentRepository;
use Request;
use Yajra\DataTables\Facades\DataTables;


class BillPaymentTableController extends Controller
{
    /**
     * variable to store the repository object
     * @var BillPaymentRepository
     */
    protected $repository;

    /**
     * contructor to initialize repository object
     * @param BillPaymentRepository $repository ;
     */
    public function __construct(BillPaymentRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * This method return the data of the model
     * @param Request $request
     * @return mixed
     */
    public function __invoke(Request $request)
    {
        $core = $this->repository->getForDataTable();

        return Datatables::of($core)
            ->escapeColumns(['id'])
            ->addIndexColumn()    
            ->addColumn('supplier', function ($billpayment) {
                if ($billpayment->supplier)
                return $billpayment->supplier->name;
            })    
            ->addColumn('account', function ($billpayment) {
                if ($billpayment->account)
                return $billpayment->account->holder;
            })
            ->addColumn('date', function ($billpayment) {
                return dateFormat($billpayment->date);
            })
            ->addColumn('amount', function ($billpayment) {
                return numberFormat($billpayment->amount);
            })
            ->addColumn('allocate', function ($billpayment) {
                return numberFormat($billpayment->allocate_ttl);
            })
            ->addColumn('bill_no', function ($billpayment) {
                return gen4tid('BILL-', $billpayment->tid);
            })
            ->addColumn('actions', function ($billpayment) {
                return $billpayment->action_buttons;
            })
            ->make(true);
    }
}
