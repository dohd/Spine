<?php

namespace App\Repositories\Focus\contract;

use App\Exceptions\GeneralException;
use App\Models\contract\Contract;
use App\Models\contract_equipment\ContractEquipment;
use App\Models\task_schedule\TaskSchedule;
use App\Repositories\BaseRepository;
use DB;
use Illuminate\Validation\ValidationException;

/**
 * Class ProductcategoryRepository.
 */
class ContractRepository extends BaseRepository
{
    /**
     * Associated Repository Model.
     */
    const MODEL = Contract::class;

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

    public function getForTaskScheduleDataTable()
    {
        return TaskSchedule::all();
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

        $contract_data = $input['contract_data'];
        foreach ($contract_data as $key => $val) {
            if ($key == 'amount') $contract_data[$key] = numberClean($val);
            if (in_array($key, ['start_date', 'end_date'])) 
                $contract_data[$key] = date_for_database($val);
        }
        $result = Contract::create($contract_data);

        $schedule_data = $input['schedule_data'];
        if (!$schedule_data) throw ValidationException::withMessages(['task schedules required!']);

        $schedule_data = array_map(function ($v) use($result) {
            return [
                'contract_id' => $result->id,
                'title' => $v['s_title'],
                'start_date' => date_for_database($v['s_start_date']),
                'end_date' => date_for_database($v['s_end_date'])
            ];
        }, $schedule_data);
        TaskSchedule::insert($schedule_data);

        $equipment_data = $input['equipment_data'];
        if (!$equipment_data) throw ValidationException::withMessages(['equipments required!']);

        $equipment_data = array_map(function ($v) use($result) {
            return $v + ['contract_id' => $result->id];
        }, $equipment_data);
        ContractEquipment::insert($equipment_data);
        
        if ($result) {
            DB::commit();
            return $result;
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
    public function update($contract, array $input)
    {
        // dd($input);
        DB::beginTransaction();

        $contract_data = $input['contract_data'];
        foreach ($contract_data as $key => $val) {
            if ($key == 'amount') $contract_data[$key] = numberClean($val);
            if (in_array($key, ['start_date', 'end_date'])) 
                $contract_data[$key] = date_for_database($val);
        }
        $result = $contract->update($contract_data);

        $schedule_data = $input['schedule_data'];        
        if (!$schedule_data) throw ValidationException::withMessages(['task schedules required!']);

        $item_ids = array_map(fn($v) => $v['s_id'], $schedule_data);
        // delete omitted schedules
        $contract->task_schedules()->whereNotIn('id', $item_ids)->where('status', 'pending')->delete();
        // create or update schedule item
        foreach ($schedule_data as $item) {
            $new_item = TaskSchedule::firstOrNew(['id' => $item['s_id']]);
            $new_item->fill([
                'contract_id' => $contract->id,
                'title' => $item['s_title'],
                'start_date' => date_for_database($item['s_start_date']),
                'end_date' => date_for_database($item['s_end_date'])
            ]);
            if (!$new_item->id) unset($new_item->id);
            $new_item->save();
        }

        $equipment_data = $input['equipment_data'];  
        if (!$equipment_data) throw ValidationException::withMessages(['equipments required!']);

        $item_ids = array_map(fn($v) => $v['contracteq_id'], $equipment_data);
        // delete omitted equipment items
        $contract->contract_equipments()->whereNotIn('id', $item_ids)->delete();
        // create or update equipment items
        foreach ($equipment_data as $item) {
            $new_item = ContractEquipment::firstOrNew(['id' => $item['contracteq_id']]);
            $new_item->fill([
                'contract_id' => $contract->id, 
                'equipment_id' => $item['equipment_id']
            ]);
            $new_item->save();
        }

        if ($result) {
            DB::commit();
            return $result;
        }

        throw new GeneralException(trans('exceptions.backend.productcategories.update_error'));
    }

    /**
     * Add Contract Equipment
     */
    public function add_equipment(array $input)
    {
        // dd($input);
        DB::beginTransaction();

        $result = ContractEquipment::insert($input);
        
        if ($result) {
            DB::commit();
            return true;
        }
        
        throw new GeneralException('Error Creating Contract');
    }

    /**
     * For deleting the respective model from storage
     *
     * @param Productcategory $productcategory
     * @throws GeneralException
     * @return bool
     */
    public function delete($contract)
    {   
        foreach ($contract->task_schedules as $schedule) {
            if ($schedule->equipments->count()) 
                throw ValidationException::withMessages(["Contract Schedule {$schedule->title} has equipments!"]);
        }

        if ($contract->delete()) return true;
        
        throw new GeneralException(trans('exceptions.backend.productcategories.delete_error'));
    }
}