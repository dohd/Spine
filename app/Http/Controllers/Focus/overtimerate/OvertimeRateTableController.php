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
namespace App\Http\Controllers\Focus\overtimerate;

use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Yajra\DataTables\Facades\DataTables;
use App\Repositories\Focus\overtimerate\OvertimeRateRepository;
//use App\Http\Requests\Focus\overtimerate\Request;

/**
 * Class overtimeratesTableController.
 */
class OvertimeRateTableController extends Controller
{
    /**
     * variable to store the repository object
     * @var overtimerateRepository
     */
    protected $overtimerate;

    /**
     * contructor to initialize repository object
     * @param OvertimeRateRepository $overtimerate ;
     */
    public function __construct(OvertimeRateRepository $overtimerate)
    {
        $this->overtimerate = $overtimerate;
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
        $core = $this->overtimerate->getForDataTable();
        return Datatables::of($core)
            ->escapeColumns(['id'])
            ->addIndexColumn()
            ->addColumn('name', function ($overtimerate) {
                  return $overtimerate->name;
                // return '<a href="' . route('biller.ji.index') . '?rel_type=2&rel_id=' . $overtimerate->id . '">' . $overtimerate->name . '</a>';
            })
            ->addColumn('rate_option', function ($overtimerate) {
                // return $overtimerate->users->count('*');
                return $overtimerate->rate_option;
            })
            ->addColumn('rate', function ($overtimerate) {
                // return $overtimerate->users->count('*');
                return $overtimerate->rate;
            })
            ->addColumn('created_at', function ($overtimerate) {
                return Carbon::parse($overtimerate->created_at)->toDateString();
            })
            ->addColumn('actions', function ($overtimerate) {
                // return '<a href="' . route('biller.hrms.index') . '?rel_type=2&rel_id=' . $overtimerate->id . '" class="btn btn-purple round" data-toggle="tooltip" data-placement="top" title="List"><i class="fa fa-list"></i></a> ' . $overtimerate->action_buttons;
                return $overtimerate->action_buttons;
            })
            ->make(true);
    }
}
