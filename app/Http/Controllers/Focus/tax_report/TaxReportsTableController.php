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
namespace App\Http\Controllers\Focus\tax_report;

use App\Http\Controllers\Controller;
use App\Repositories\Focus\tax_report\TaxReportRepository;
use Request;
use Yajra\DataTables\Facades\DataTables;


class TaxReportsTableController extends Controller
{
    /**
     * variable to store the repository object
     * @var TaxReportRepository
     */
    protected $repository;

    /**
     * contructor to initialize repository object
     * @param TaxReportRepository $repository ;
     */
    public function __construct(TaxReportRepository $repository)
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
            ->addColumn('date', function ($report) {
                return dateFormat($report->createdAt);
            })
            ->addColumn('actions', function ($report) {
                return $report->action_buttons;
            })
            ->make(true);
    }
}
