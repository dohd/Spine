<?php
/* Copyright (c) UltimateKode.com. All Rights Reserved
 * ***********************************************************************
 *
 *  Email: support@ultimatekode.com
 *  Website: https://www.ultimatekode.com
 **
 * Rose Business Suite - Accounting, CRM and POS Software
 
 *  ************************************************************************
 *  * This software is furnished under a license and may be used and copied
 *  * only  in  accordance  with  the  terms  of such  license and with the
 *  * inclusion of the above copyright notice.
 *  * If you Purchased from Codecanyon, Please read the full License from
 *  * here- http://codecanyon.net/licenses/standard/
 * ***********************************************************************
 */

namespace App\Http\Controllers\Focus\bills;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Focus\bills\BillsController;
use Yajra\DataTables\Facades\DataTables;

/**
 * Class BranchTableController.
 */
class BillsTableController extends Controller
{
    /**
     * This method return the data of the model
     * @return mixed
     */
    public function __invoke()
    {
        $core = BillsController::getForDataTable();

        return Datatables::of($core)
            ->escapeColumns(['id'])
            ->addIndexColumn()
            ->addColumn('supplier', function ($bill) {
                if ($bill->supplier_id) return $bill->supplier->name;

                return $bill->supllier_name;
            })
            ->addColumn('document', function ($bill) {
                return $bill->doc_ref_type . ' - ' . $bill->doc_ref; 
            })
            ->addColumn('date', function ($bill) {
                return dateFormat($bill->date); 
            })
            ->addColumn('due_date', function ($bill) {
                return dateFormat('due_date'); 
            })
            ->make(true);
    }
}
