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
namespace App\Http\Controllers\Focus\payroll;

use Carbon\Carbon;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use App\Repositories\Focus\payroll\PayrollRepository;

/**
 * Class payrollsTableController.
 */
class PayrollTableController extends Controller
{
    /**
     * variable to store the repository object
     * @var payrollRepository
     */
    protected $payroll;

    /**
     * contructor to initialize repository object
     * @param payrollRepository $payroll ;
     */
    public function __construct(PayrollRepository $payroll)
    {
        $this->payroll = $payroll;
    }

    /**
     * This method return the data of the model
     * @param ManagepayrollRequest $request
     *
     * @return mixed
     */
    public function __invoke(Request $request)
    {
        //
        $core = $this->payroll->getForDataTable();
        return Datatables::of($core)
            ->escapeColumns(['id'])
            ->addIndexColumn()
            ->addColumn('tid', function ($payroll) {
               return gen4tid('PYRLL-', $payroll->tid);
            })
            ->addColumn('processing_date', function ($payroll) {
                return dateFormat($payroll->processing_date);
            })
            ->addColumn('payroll_month', function ($payroll) {
                return dateFormat($payroll->payroll_month);
            })
            ->addColumn('status', function ($payroll) {
                return ucfirst($payroll->status);
            })
            ->addColumn('actions', function ($payroll) {
                return $payroll->actions_buttons;
            })
            ->make(true);
    }
}
