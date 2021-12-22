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
class StockIssuanceTableController extends Controller
{
    /**
     * This method return the data of the model
     * @return mixed
     */
    public function __invoke()
    {
        $core = StockIssuanceController::getForDataTable();

        return Datatables::of($core)
            ->addIndexColumn()
            ->addColumn('notes', function($quote) {
                return $quote->notes;
            })
            ->addColumn('tid', function ($quote) {
                $tid = sprintf('%04d', $quote->tid);
                if ($quote->bank_id) $tid = 'PI-'.$tid;
                else $tid = 'QT-'.$tid;

                return $tid;               
            })
            ->addColumn('customer', function ($quote) {
                if (isset($quote->customer) && isset($quote->lead->branch)) {
                    return $quote->customer->name.' - '.$quote->lead->branch->name;
                }
                
                return $quote->lead->client_name;
            })
            ->addColumn('quote_date', function ($quote) {
                return dateFormat($quote->invoicedate);
            })
            ->addColumn('project_number', function($quote) {
                $tid = '';
                if (isset($quote->project_quote->project)) {
                    $tid = 'Prj-'.sprintf('%04d', $quote->project_quote->project->project_number);
                }
                return $tid;
            })
            ->addColumn('total', function ($quote) {
                return number_format($quote->total, 2);
            })
            ->addColumn('actions', function ($quote) {
                return '<a href="#" class="btn btn-primary round" data-toggle="tooltip" data-placement="top" title="Verify"><i class="fa fa-check"></i></a>';
            })
            ->rawColumns(['notes', 'tid', 'customer', 'actions', 'status', 'total'])
            ->make(true);
    }
}
