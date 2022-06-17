<?php

namespace App\Repositories\Focus\taskschedule;

use App\Exceptions\GeneralException;
use App\Models\contract_equipment\ContractEquipment;
use App\Models\contractservice\ContractService;
use App\Models\equipment\Equipment;
use App\Models\items\ServiceItem;
use App\Models\task_schedule\TaskSchedule;
use App\Repositories\BaseRepository;
use DB;

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
        return $this->query()->get();
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
        // existing equipments id
        $eq_ids = ContractEquipment::where([
            'contract_id' => $data['contract_id'], 
            'schedule_id' => $data['schedule_id']
        ])->pluck('equipment_id')->toArray();

        // load unique equipments
        $data_items = array();
        foreach ($input['data_items'] as $item) {
            $item = $data + ['equipment_id' => $item['equipment_id']];
            if (!in_array($item['equipment_id'], $eq_ids)) $data_items[] = $item;
        }
        $result = ContractEquipment::insert($data_items);

        $schedule = TaskSchedule::find($data['schedule_id']);
        // update schedule status to loaded
        if ($data_items && $schedule->status == 'pending') 
            $schedule->update(['status' => 'loaded']);

        // generate service
        if ($data_items) {
            $service_amount = array_reduce($input['data_items'], function($init, $item) {
                return $init + floatval($item['service_rate']);
            }, 0);

            // update existing service equipments
            $service = ContractService::where('schedule_id', $schedule->id)->first();
            if ($service) {
                $service->increment('amount', $service_amount);
            } else {
                // create new service
                $service_data = $data + [
                    'name' => $schedule->title . ' - ' . $schedule->contract->title,
                    'amount' => $service_amount,
                    'ins' => auth()->user()->ins,
                    'user_id' => auth()->user()->id
                ];
                $service = ContractService::create($service_data);
            }

            // generate service date 
            $service_date = $this->gen_service_date($schedule, $service);
            // generate service equipments
            $service_id = $service->id;
            $items_data = array_map(function ($v) use($service_id, $service_date) {
                return $v + [
                    'service_id' => $service_id,
                    'last_service_date' => $service_date[0],
                    'next_service_date' => $service_date[1],
                ];
            }, $input['data_items']);
            ServiceItem::insert($items_data);
        }
        
        DB::commit();
        if ($result) return $result;

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
        $data['start_date'] = date_for_database($data['start_date']);
        $data['end_date'] = date_for_database($data['end_date']);
        $result = $taskschedule->update($data);

        // delete omitted items
        $data_items = $input['data_items'];
        $eq_ids = array_map(function ($v) { return $v['id']; }, $data_items);
        $taskschedule->taskschedule_equipments()->whereNotIn('id', $eq_ids)->delete();

        // update service 
        $service = $taskschedule->contractservice;
        if ($service) $service->update(['name' => $taskschedule->title . ' - ' . $taskschedule->contract->title]);

        DB::commit();
        if ($result) return $result;

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
        DB::beginTransaction();

        $service = $taskschedule->contractservice;
        if ($service) {
            if ($service->items->where('jobcard_no', '>', 0)->count()) return false;
            $service->items()->delete();
            $service->delete();
        }
        $taskschedule->taskschedule_equipments()->delete();
        $result = $taskschedule->delete();

        DB::commit();
        if ($result) return true;

        throw new GeneralException(trans('exceptions.backend.productcategories.delete_error'));
    }
}