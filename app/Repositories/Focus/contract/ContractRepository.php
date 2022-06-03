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
    public function update(Contract $contract, array $data)
    {
        throw new GeneralException(trans('exceptions.backend.productcategories.update_error'));
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