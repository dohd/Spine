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

        $c_data = $input['contract_data'];
        foreach ($c_data as $k => $val) {
            if (in_array($k, ['amount', 'start_date', 'end_date'])) {
                if ($k == 'amount') $c_data[$k] = numberClean($val);
                else $c_data[$k] = date_for_database($val);
            }
        }
        $result = Contract::create($c_data);
        $contract_id = $result->id;

        $s_data = $input['schedule_data'];
        $s_data = array_map(function ($v) use($contract_id) {
            return [
                'contract_id' => $contract_id,
                'title' => $v['s_title'],
                'start_date' => date_for_database($v['s_start_date']),
                'end_date' => date_for_database($v['s_end_date'])
            ];
        }, $s_data);
        TaskSchedule::insert($s_data);

        $eq_data = $input['equipment_data'];
        $eq_data = array_map(function ($v) use($contract_id) {
            return ['contract_id' => $contract_id] + $v;
        }, $eq_data);
        ContractEquipment::insert($eq_data);
        
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

        $c_data = $input['contract_data'];
        foreach ($c_data as $k => $val) {
            if (in_array($k, ['amount', 'start_date', 'end_date'])) {
                if ($k == 'amount') $c_data[$k] = numberClean($val);
                else $c_data[$k] = date_for_database($val);
            }
        }
        $result = $contract->update($c_data);

        $s_data = $input['schedule_data'];        
        // delete omitted items
        $s_ids = array_map(function ($v) { return $v['s_id']; }, $s_data);
        $contract->task_schedules()->whereNotIn('id', $s_ids)->where('status', 'pending')->delete();
        // create or update item
        foreach ($s_data as $v) {
            $v = [
                'contract_id' => $contract->id,
                'id' => $v['s_id'],
                'title' => $v['s_title'],
                'start_date' => date_for_database($v['s_start_date']),
                'end_date' => date_for_database($v['s_end_date'])
            ];
            $item = TaskSchedule::firstOrNew(['id' => $v['id']]);
            foreach ($v as $key => $val) {
                $item[$key] = $val;
            }
            if (!$item->id) unset($item->id);
            $item->save();
        }

        $eq_data = $input['equipment_data'];
        // delete omitted items
        $eq_ids = array_map(function ($v) { return $v['contracteq_id']; }, $eq_data);
        $contract->contract_equipments()->whereNotIn('id', $eq_ids)->delete();
        // create or update item
        foreach ($eq_data as $v) {
            $v = [
                'contract_id' => $contract->id, 
                'id' => $v['contracteq_id'],
                'equipment_id' => $v['equipment_id']
            ];
            $item = ContractEquipment::firstOrNew(['id' => $v['id']]);
            foreach ($v as $key => $val) {
                $item[$key] = $val;
            }
            if (!$item->id) unset($item->id);
            $item->save();
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
        $data = $input['data'];

        // existing equipments id
        $eq_ids = array();
        if (isset($data['contract_id']) && isset($data['schedule_id'])) {
            $eq_ids = ContractEquipment::where([
                'contract_id' => $data['contract_id'], 
                'schedule_id' => $data['schedule_id']
            ])->pluck('equipment_id')->toArray();
        } elseif (isset($data['contract_id'])) {
            $eq_ids = ContractEquipment::where([
                'contract_id' => $data['contract_id'], 
                'schedule_id' => 0
            ])->pluck('equipment_id')->toArray();
        }

        // load unique equipments
        $data_items = array();
        foreach ($input['data_items'] as $item) {
            $item = $data + ['equipment_id' => $item['equipment_id']];
            if (!in_array($item['equipment_id'], $eq_ids)) $data_items[] = $item;
        }
        $result = ContractEquipment::insert($data_items);
        if ($result) return $result;

        throw new GeneralException('Error Creating Contract');
    }

    /**
     * For deleting the respective model from storage
     *
     * @param Productcategory $productcategory
     * @throws GeneralException
     * @return bool
     */
    public function delete(Contract $contract)
    {   
        throw new GeneralException(trans('exceptions.backend.productcategories.delete_error'));
    }
}