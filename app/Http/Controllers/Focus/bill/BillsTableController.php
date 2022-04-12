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

namespace App\Http\Controllers\Focus\bill;

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
            ->addColumn('tid', function ($bill) {
                $tid = 'BILL-'.sprintf('%04d', $bill->tid);
                $link = $bill->po_id ? route('biller.purchaseorders.show', [$bill->purchaseorder->id]) : route('biller.purchases.show', [$bill->id]);
                return '<a class="font-weight-bold" href="' . $link . '">' . $tid . '</a>';
            })
            ->addColumn('amount', function ($bill) {
                return number_format($bill->grandttl, 2);
            })
            ->addColumn('paid', function ($bill) {
                if ($bill->paidbill) return number_format($bill->paidbill->paid, 2);
            })
            ->addColumn('status', function ($bill) {
                $badge = 'secondary';
                switch ($bill->status) {
                    case 'Paid': 
                        $badge = 'success';
                        break;
                    case 'Partial':
                        $badge = 'primary';
                        break;
                }
                return '<span class="badge badge-'.$badge.'">'.$bill->status.'</span>';
            })
            ->addColumn('supplier', function ($bill) {
                if ($bill->supplier_id) return $bill->supplier->name;
                return $bill->supllier_name;
            })
            ->addColumn('document', function ($bill) {
                if ($bill->po_id) 
                    return $bill->purchaseorder->doc_ref_type . ' - ' . $bill->purchaseorder->doc_ref;
                return $bill->doc_ref_type . ' - ' . $bill->doc_ref; 
            })
            ->addColumn('date', function ($bill) {
                if ($bill->po_id) 
                    return dateFormat($bill->purchaseorder->date);
                return dateFormat($bill->date); 
            })
            ->addColumn('due_date', function ($bill) {
                if ($bill->po_id) return dateFormat($bill->purchaseorder->due_date); 
                return dateFormat('due_date'); 
            })
            ->make(true);
    }
}
