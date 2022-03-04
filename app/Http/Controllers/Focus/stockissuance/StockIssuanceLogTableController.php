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
namespace App\Http\Controllers\Focus\stockissuance;

use App\Http\Controllers\Controller;
use Yajra\DataTables\Facades\DataTables;

/**
 * Class QuotesTableController.
 */
class StockIssuanceLogTableController extends Controller
{
    /**
     * This method return the data of the model
     * @return mixed
     */
    public function __invoke()
    {        
        $core = StockIssuanceController::stockissuanceLogDataTable();

        return Datatables::of($core)
            ->escapeColumns(['id'])
            ->addIndexColumn()
            ->addColumn('name', function ($log) {
                return $log->budget_item->product_name;
            })
            ->addColumn('unit', function ($log) {
                return $log->budget_item->unit;
            })
            ->addColumn('date', function ($log) {
                return dateFormat($log->created_at);
            })
            ->addColumn('actions', function ($log) {
                return '<button type="button" class="btn btn-link delete-log" data-id="'.$log->id.'">
                        <i class="fa fa-trash fa-lg text-danger"></i>
                    </button>';
            })
            ->make(true);
    }
}
