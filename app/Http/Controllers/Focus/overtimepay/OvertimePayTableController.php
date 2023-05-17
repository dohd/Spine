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
namespace App\Http\Controllers\Focus\overtimepay;

use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Yajra\DataTables\Facades\DataTables;
use App\Repositories\Focus\overtimepay\OvertimePayRepository;
//use App\Http\Requests\Focus\overtimepay\Request;

/**
 * Class overtimepaysTableController.
 */
class OvertimePayTableController extends Controller
{
    /**
     * variable to store the repository object
     * @var overtimepayRepository
     */
    protected $overtimepay;

    /**
     * contructor to initialize repository object
     * @param OvertimePayRepository $overtimepay ;
     */
    public function __construct(OvertimePayRepository $overtimepay)
    {
        $this->overtimepay = $overtimepay;
    }

    /**
     * This method return the data of the model
     * @param Request $request
     *
     * @return mixed
     */
    public function __invoke(Request $request)
    {
        //
        $core = $this->overtimepay->getForDataTable();
        return Datatables::of($core)
            ->escapeColumns(['id'])
            ->addIndexColumn()
            ->addColumn('employee_id', function ($overtimepay) {
                  return $overtimepay->employee_id;
            })
            ->addColumn('date', function ($overtimepay) {
                return dateFormat($overtimepay->date);
            })
            ->addColumn('clock_in', function ($overtimepay) {
                return $overtimepay->clock_in;
            })
            ->addColumn('clock_out', function ($overtimepay) {
                return $overtimepay->clock_out;
            })
            ->addColumn('created_at', function ($overtimepay) {
                return Carbon::parse($overtimepay->created_at)->toDateString();
            })
            ->addColumn('actions', function ($overtimepay) {
                return $overtimepay->action_buttons;
            })
            ->make(true);
    }
}
