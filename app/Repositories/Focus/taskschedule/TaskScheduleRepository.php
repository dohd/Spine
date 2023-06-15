<?php

namespace App\Repositories\Focus\taskschedule;

use App\Exceptions\GeneralException;
use App\Models\contract_equipment\ContractEquipment;
use App\Models\task_schedule\TaskSchedule;
use App\Repositories\BaseRepository;
use DB;
use Illuminate\Validation\ValidationException;

/**
 * Class ProductcategoryRepository.
 */
class TaskScheduleRepository extends BaseRepository
{
    /**
     * Associated Repository Model.
     */
    const MODEL = TaskSchedule::class;

    /**
     * This method is used by Table Controller
     * For getting the table data to show in
     * the grid
     * @return mixed
     */
    public function getForDataTable()
    {
        $q = $this->query();

        $q->when(request('customer_id'), function ($q) {
            $q->whereHas('contract', fn($q) => $q->where('customer_id', request('customer_id')));
        })->when(request('contract_id'), fn($q) => $q->where('contract_id', request('contract_id')))
        ->when(request('equip_status') == 'unserviced', function ($q) {
            $q->doesntHave('contractservices')->whereHas('equipments');
        })->when(in_array(request('service_status'), ['partially_serviced', 'serviced']), function ($q) {
            $q->whereHas('contractservices')->whereHas('equipments');
        });

        return $q;
    }

    /**
     * For Creating the respective model in storage
     *
     * @param array $input
     * @throws GeneralException
     * @return bool
     */
    public function create(array $input)
    {
        // dd($input);
        DB::beginTransaction();

        $data = $input['data'];
        // update schedule status to loaded
        $schedule = TaskSchedule::find($data['schedule_id']);
        $schedule->update([
            'status' => 'loaded',
            'actual_startdate' => date_for_database($data['actual_startdate']),
            'actual_enddate' => date_for_database($data['actual_enddate']),
        ]);

        $data_items = $input['data_items'];
        $data_items = array_map(function ($v) use($data) {
            return array_replace($v, [
                'contract_id' => $data['contract_id'],
                'schedule_id' => $data['schedule_id']
            ]);
        }, $data_items);
        ContractEquipment::insert($data_items);
        
        if ($schedule) {
            DB::commit();
            return $schedule;
        }

        throw new GeneralException('Error Creating Contract');
    }

    /**
     * For updating the respective Model in storage
     *
     * @param Productcategory $productcategory
     * @param  $input
     * @throws GeneralException
     * return bool
     */
    public function update($taskschedule, array $input)
    {
        // dd($input); 
        DB::beginTransaction();

        $data = $input['data'];
        foreach ($data as $key => $val) {
            $dates = ['start_date', 'end_date', 'actual_startdate', 'actual_enddate'];
            if (in_array($key, $dates)) $data[$key] = date_for_database($val);
        }

        $result = false;
        if (isset($data['is_copy'])) {
            $is_loaded = ContractEquipment::where([
                'contract_id' => $taskschedule->contract_id,
                'schedule_id' => $data['schedule_id'],
            ])->where('equipment_id', '>', 0)->count();
            if ($is_loaded) throw ValidationException::withMessages(['Equipments already loaded to this schedule!']);

            $prev_schedule_equipments = ContractEquipment::where([
                'contract_id' => $taskschedule->contract_id,
                'schedule_id' => $taskschedule->id,
            ])->get(['contract_id', 'schedule_id', 'equipment_id'])->toArray();

            $schedule = TaskSchedule::find($data['schedule_id']);
            $copy_schedule_equipments = array_map(function ($v) use($schedule) {
                $v['schedule_id'] = $schedule->id;
                return $v;
            }, $prev_schedule_equipments);
            
            ContractEquipment::insert($copy_schedule_equipments);
            $result = $schedule->update(['status' => 'loaded']);
        } else {
            $result = $taskschedule->update($data);
            // delete omitted equipment items
            $data_items = $input['data_items'];
            $item_ids = array_map(fn($v) => $v['id'], $data_items);
            $taskschedule->contract_equipments()->whereNotIn('equipment_id', $item_ids)->delete();
        }

        if ($result) {
            DB::commit();
            return $result;
        }

        throw new GeneralException(trans('exceptions.backend.productcategories.update_error'));
    }

    public function gen_service_date($schedule, $service)
    {
        $service_date = array();
        $schedules = TaskSchedule::where('contract_id', $schedule->contract_id)->get(['id', 'start_date', 'end_date']);
        foreach ($schedules as $i => $item) {
            if ($item['id'] == $service->schedule_id) {
                if ($i > 0) {
                    $service_date = [
                        $schedules[$i - 1]['end_date'], 
                        $schedules[$i]['start_date']
                    ];
                } 
                else $service_date = [null, $schedules[$i]['start_date']]; 
            }
        }

        return $service_date;
    }

    /**
     * For deleting the respective model from storage
     *
     * @param Productcategory $productcategory
     * @throws GeneralException
     * @return bool
     */
    public function delete($taskschedule)
    {
        if ($taskschedule->contractservice) {
            $service = $taskschedule->contractservice;
            $msg = "Schedule is attached to service report! Jobcard No. {$service->jobcard_no}";
            throw ValidationException::withMessages([$msg]);
        }
            
        if ($taskschedule->delete()) return true;

        throw new GeneralException(trans('exceptions.backend.productcategories.delete_error'));
    }
}