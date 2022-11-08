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

    protected $service_status;

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
                    $link = '<a href="'. route('biller.contracts.show', $contract).'">'.$name.'</a>';
                }
                return $link;
                
            })->addColumn('loaded', function ($schedule) {
                $serviced_equip_ids = array();
                foreach ($schedule->contractservices as $service) {
                    $equip_ids = $service->items()->pluck('equipment_id')->toArray();
                    $serviced_equip_ids = array_merge($serviced_equip_ids, $equip_ids);
                }
                $schedule_equip_ids = $schedule->equipments()->get(['equipments.id'])->pluck('id')->toArray();
                
                $schedule_units = count($schedule_equip_ids);
                $serviced_units = count($serviced_equip_ids);
                $unserviced_units = count(array_diff($schedule_equip_ids, $serviced_equip_ids));

                // service status
                if ($serviced_units && $serviced_units >= $schedule_units) {
                    $this->service_status = 'complete';
                } elseif ($serviced_units && $serviced_units < $schedule_units) {
                    $this->service_status = 'partial';
                } else $this->service_status = 'unserviced';
                    
                $params = [
                    'contract_id' => $schedule->contract? $schedule->contract->id : '',
                    'customer_id' => $schedule->contract? $schedule->contract->customer_id : '', 
                    'schedule_id' => $schedule->id,
                    'is_serviced' => 0,
                ];
                $unserviced_link = '<a href="'. route('biller.equipments.index', $params) .'">unserviced:</a>';

                return "{$unserviced_link} <b>{$unserviced_units}/{$schedule_units}</b> <br> serviced: <b>{$serviced_units}/{$schedule_units}</b>";
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
            ->addColumn('service_status', function ($schedule) {
                return $this->service_status;
            })
            ->addColumn('actions', function ($schedule) {
                $params = ['customer_id' => $schedule->contract->customer_id, 'schedule_id' => $schedule->id];
                return $schedule->action_buttons 
                    . ' <a class="btn btn-purple round" href="'. route('biller.equipments.index', $params) .'" title="equipments"><i class="fa fa-list"></i></a> '; 
                    
            })
            ->make(true);
    }
}
