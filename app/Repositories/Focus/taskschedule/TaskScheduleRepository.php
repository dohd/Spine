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
        // update schedule status to loaded
        $schedule = TaskSchedule::find($data['schedule_id']);
        if ($schedule->status == 'pending') $schedule->update(['status' => 'loaded']);
        if (!$schedule->actual_startdate || !$schedule->actual_enddate)
            $schedule->update([
                'actual_startdate' => date_for_database($data['actual_stardate']),
                'actual_enddate' => date_for_database($data['actual_enddate']),
            ]);

        // fetch existing equipments ids
        $fields = array_intersect_key($data, array_flip(['contract_id', 'schedule_id']));
        $item_ids = ContractEquipment::where($fields)->pluck('equipment_id')->toArray();

        // filter out duplicates
        $data_items = $input['data_items'];
        $data_items = array_reduce($data_items, function ($init, $v) use($item_ids, $fields) {
            if (!in_array($v['equipment_id'], $item_ids)) $init[] = $v + $fields;
            return $init;
        }, []);
        
        if (!$data_items) throw ValidationException::withMessages(['Equipments already loaded!']);
        ContractEquipment::insert($data_items);
        
        DB::commit();
        if ($schedule) return $schedule;

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
        $result = $taskschedule->update($data);

        // delete omitted items
        $data_items = $input['data_items'];
        $item_ids = array_map(function ($v) { return $v['id']; }, $data_items);
        $taskschedule->contract_equipment()->whereNotIn('id', $item_ids)->delete();

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
        if ($taskschedule->delete()) return true;

        throw new GeneralException(trans('exceptions.backend.productcategories.delete_error'));
    }
}