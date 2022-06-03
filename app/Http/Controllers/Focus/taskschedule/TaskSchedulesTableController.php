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

namespace App\Http\Controllers\Focus\taskschedule;

use App\Http\Controllers\Controller;
use App\Repositories\Focus\taskschedule\TaskScheduleRepository;
use Yajra\DataTables\Facades\DataTables;

/**
 * Class BranchTableController.
 */
class TaskSchedulesTableController extends Controller
{
    /**
     * variable to store the repository object
     * @var TaskScheduleRepository
     */
    protected $schedule;

    /**
     * contructor to initialize repository object
     * @param TaskScheduleRepository $schedule;
     */
    public function __construct(TaskScheduleRepository $schedule)
    {
        $this->schedule = $schedule;
    }

    /**
     * This method return the data of the model
     * @return mixed
     */
    public function __invoke()
    {
        $core = $this->schedule->getForDataTable();

        return Datatables::of($core)
            ->escapeColumns(['id'])
            ->addIndexColumn()
            ->addColumn('contract_tid', function ($schedule) {        
                if ($schedule->schedule)
                return $schedule->schedule->title;
            })
            ->addColumn('schedule', function ($schedule) {
                return $schedule->title;
            })
            ->addColumn('start_date', function ($schedule) {
                return dateFormat($schedule->start_date);
            })
            ->addColumn('end_date', function ($schedule) {
                return dateFormat($schedule->end_date);
            })
            ->addColumn('actions', function ($schedule) {
                return $schedule->action_buttons;
            })
            ->make(true);
    }
}
