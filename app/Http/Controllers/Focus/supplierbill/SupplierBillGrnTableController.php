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
use App\Repositories\Focus\goodsreceivenote\GoodsreceivenoteRepository;
use Request;
use Yajra\DataTables\Facades\DataTables;


class SupplierBillGrnTableController extends Controller
{
    /**
     * variable to store the repository object
     * @var GoodsreceivenoteRepository
     */
    protected $repository;

    /**
     * contructor to initialize repository object
     * @param GoodsreceivenoteRepository $repository ;
     */
    public function __construct(GoodsreceivenoteRepository $repository)
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
            ->addColumn('mass_select', function ($grn) {
                return  '<input type="checkbox"  class="check-row" value="'. $grn->id .'">';
            })
            ->addColumn('tid', function ($grn) {
                return gen4tid('GRN-', $grn->tid);
            })
            ->addColumn('supplier', function ($grn) {
                if ($grn->supplier)
                return $grn->supplier->name;
            })        
            ->addColumn('purchase_type', function ($grn) {
                $purchaseorder = $grn->purchaseorder;
                if ($purchaseorder) {
                    $lpo_no = gen4tid('PO-', $purchaseorder->tid);
                    $note = $purchaseorder->note;

                    return $lpo_no . ' - ' . $note;
                }
            })
            ->addColumn('dnote', function ($grn) {
                return $grn->dnote;
            })
            ->addColumn('date', function ($grn) {
                return dateFormat($grn->date);
            })
            ->make(true);
    }
}
