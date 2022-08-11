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
namespace App\Http\Controllers\Focus\supplierbill;

use App\Http\Controllers\Controller;
use App\Repositories\Focus\supplierbill\SupplierBillRepository;
use Request;
use Yajra\DataTables\Facades\DataTables;


class SupplierBillTableController extends Controller
{
    /**
     * variable to store the repository object
     * @var SupplierBillRepository
     */
    protected $repository;

    /**
     * contructor to initialize repository object
     * @param SupplierBillRepository $repository ;
     */
    public function __construct(SupplierBillRepository $repository)
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
            ->addColumn('tid', function ($supplierbill) {
                return gen4tid('BILL-', $supplierbill->tid);
            })
            ->addColumn('supplier', function ($supplierbill) {
                if ($supplierbill->supplier)
                return $supplierbill->supplier->name;
            })        
            ->addColumn('total', function ($supplierbill) {
                return numberFormat($supplierbill->total);
            })
            ->addColumn('balance', function ($supplierbill) {
                return numberFormat($supplierbill->total - $supplierbill->amountpaid);
            })
            ->addColumn('status', function ($supplierbill) {
                return $supplierbill->status;
            })
            ->addColumn('due_date', function ($supplierbill) {
                return dateFormat($supplierbill->due_date);
            })
            ->addColumn('actions', function ($supplierbill) {
                return $supplierbill->action_buttons;
            })
            ->make(true);
    }
}
