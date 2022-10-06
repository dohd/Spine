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
            ->addColumn('contract', function ($schedule) {   
                $link = '';
                $contract = $schedule->contract;
                if ($contract && $contract->customer) {
                    $name = "{$contract->title} - {$contract->customer->company}";
                    $link = '<a href="'. route('biller.contracts.edit', $contract).'">'.$name.'</a>';
                }
                return $link;
                
            })->addColumn('loaded', function ($schedule) {
                return $schedule->equipments->count();
            })
            ->addColumn('unserviced', function ($schedule) {
                $serviced_units = 0;
                $service = $schedule->contractservice;
                if ($service) $serviced_units = $service->items->count();
                    
                return $schedule->equipments->count() - $serviced_units;
            })
            ->addColumn('total_rate', function ($schedule) {
                return numberFormat($schedule->equipments->sum('service_rate'));
            })
            ->addColumn('total_charged', function ($schedule) {
                return numberFormat($schedule->equipments->sum('service_rate'));
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
