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
namespace App\Http\Controllers\Focus\utility_bill;

use App\Http\Controllers\Controller;
use App\Repositories\Focus\utility_bill\UtilityBillRepository;
use Request;
use Yajra\DataTables\Facades\DataTables;


class UtilityBillTableController extends Controller
{
    /**
     * variable to store the repository object
     * @var UtilityBillRepository
     */
    protected $repository;

    /**
     * contructor to initialize repository object
     * @param UtilityBillRepository $repository ;
     */
    public function __construct(UtilityBillRepository $repository)
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
            ->addColumn('tid', function ($utility_bill) {
                return gen4tid('BILL-', $utility_bill->tid);
            })
            ->addColumn('supplier', function ($utility_bill) {
                if ($utility_bill->supplier)
                return $utility_bill->supplier->name;
            })        
            ->addColumn('total', function ($utility_bill) {
                return numberFormat($utility_bill->total);
            })
            ->addColumn('balance', function ($utility_bill) {
                return numberFormat($utility_bill->total - $utility_bill->amountpaid);
            })
            ->addColumn('status', function ($utility_bill) {
                return $utility_bill->status;
            })
            ->addColumn('due_date', function ($utility_bill) {
                return dateFormat($utility_bill->due_date);
            })
            ->addColumn('actions', function ($utility_bill) {
                return $utility_bill->action_buttons;
            })
            ->make(true);
    }
}
