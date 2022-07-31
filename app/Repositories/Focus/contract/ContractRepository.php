<?php

namespace App\Repositories\Focus\contract;

use App\Exceptions\GeneralException;
use App\Models\contract\Contract;
use App\Models\contract_equipment\ContractEquipment;
use App\Models\task_schedule\TaskSchedule;
use App\Repositories\BaseRepository;
use DB;

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
        foreach ($contract_data as $k => $val) {
            if ($k == 'amount') $contract_data[$k] = numberClean($val);
            if (in_array($k, ['start_date', 'end_date'])) 
                $contract_data[$k] = date_for_database($val);
        }
        $result = Contract::create($contract_data);

        $schedule_data = $input['schedule_data'];
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
        $equipment_data = array_map(function ($v) use($result) {
            return $v + ['contract_id' => $result->id];
        }, $equipment_data);
        ContractEquipment::insert($equipment_data);
        
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
    public function update($contract, array $input)
    {
        // dd($input);
        DB::beginTransaction();

        $contract_data = $input['contract_data'];
        foreach ($contract_data as $k => $val) {
            if (in_array($k, ['amount', 'start_date', 'end_date'])) {
                if ($k == 'amount') $contract_data[$k] = numberClean($val);
                else $contract_data[$k] = date_for_database($val);
            }
        }
        $result = $contract->update($contract_data);

        $schedule_data = $input['schedule_data'];        
        // delete omitted items
        $item_ids = array_map(function ($v) { return $v['s_id']; }, $schedule_data);
        $contract->task_schedules()->whereNotIn('id', $item_ids)->where('status', 'pending')->delete();
        // create or update item
        foreach ($schedule_data as $item) {
            $item = [
                'id' => $item['s_id'],
                'contract_id' => $contract->id,
                'title' => $item['s_title'],
                'start_date' => date_for_database($item['s_start_date']),
                'end_date' => date_for_database($item['s_end_date'])
            ];
            $new_item = TaskSchedule::firstOrNew(['id' => $item['id']]);
            $new_item->fill($item);
            if (!$new_item->id) unset($new_item->id);
            $new_item->save();
        }

        $equipment_data = $input['equipment_data'];
        // delete omitted items
        $item_ids = array_map(function ($v) { return $v['contracteq_id']; }, $equipment_data);
        $contract->contract_equipment()->whereNotIn('equipment_id', $item_ids)->delete();
        // create or update item
        foreach ($equipment_data as $item) {
            $item = [
                'id' => $item['contracteq_id'],
                'contract_id' => $contract->id, 
                'equipment_id' => $item['equipment_id']
            ];
            $new_item = ContractEquipment::firstOrNew(['id' => $item['id']]);
            $new_item->fill($item);
            if (!$new_item->id) unset($new_item->id);
            $new_item->save();
        }

        DB::commit();
        if ($result) return $result;

        throw new GeneralException(trans('exceptions.backend.productcategories.update_error'));
    }

    /**
     * Add Contract Equipment
     */
    public function add_equipment(array $input)
    {
        // dd($input);
        DB::beginTransaction();

        foreach ($input as $equiment) {
            $item = ContractEquipment::firstOrNew($equiment);
            $item->save();            
        }
        
        DB::commit();
        if ($input) return true;
        
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
        if ($contract->delete()) return true;
        
        throw new GeneralException(trans('exceptions.backend.productcategories.delete_error'));
    }
}