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
use App\Repositories\Focus\bill\BillRepository;
use Yajra\DataTables\Facades\DataTables;

/**
 * Class BranchTableController.
 */
class BillsTableController extends Controller
{
    /**
     * variable to store the repository object
     * @var BillRepository
     */
    protected $bill;

    /**
     * contructor to initialize bill object
     * @param BillRepository $bill ;
     */
    public function __construct(BillRepository $bill)
    {
        $this->bill = $bill;
    }

    /**
     * This method return the data of the model
     * @return mixed
     */
    public function __invoke()
    {
        $core = $this->bill->getForDataTable();

        return Datatables::of($core)
            ->escapeColumns(['id'])
            ->addIndexColumn()
            ->addColumn('amount', function ($bill) {
                return number_format($bill->grandttl, 2);
            })
            ->addColumn('paid', function ($bill) {
                if ($bill->paidbill) return number_format($bill->paidbill->paid, 2);
            })
            ->addColumn('status', function ($bill) {
                $status = ['secondary', 'Pending'];
                if ($bill->paidbill) {
                    $status =  ['success', 'Paid'];
                    $paid = $bill->paidbill->paid;
                    if ($paid < $bill->grandttl) $status = ['primary', 'Partial'];
                }
                return '<span class="badge badge-'.$status[0].'">'.$status[1].'</span>';
            })
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
