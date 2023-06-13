<?php

namespace App\Repositories\Focus\salary;

use DB;
use Carbon\Carbon;
use App\Models\salary\Salary;
use App\Exceptions\GeneralException;

use App\Models\allowance_employee\AllowanceEmployee;
use App\Repositories\BaseRepository;
use Illuminate\Database\Eloquent\Model;

/**
 * Class salaryRepository.
 */
class SalaryRepository extends BaseRepository
{
    /**
     * Associated Repository Model.
     */
    const MODEL = Salary::class;

    /**
     * This method is used by Table Controller
     * For getting the table data to show in
     * the grid
     * @return mixed
     */
    public function getForDataTable()
    {

        return $this->query()->latest()
            ->get()->unique('employee_id');
    }

    /**
     * For Creating the respective model in storage
     *
     * @param array $input
     * @throws GeneralException
     * @return bool
     */
    public function create( $input)
    {
       
        $createsalary = Salary::create($input['input']);
        
        $allarr= $input['employee_allowance'];
        $allarr = array_map(function ($v) use($createsalary) {
            
            return array_replace($v, [
                'contract_id' => $createsalary->id,
                'user_id'=> auth()->user()->id,
                'ins'=> auth()->user()->ins,
            ]);
        }, $allarr);
        if ($createsalary) {
            AllowanceEmployee::insert($allarr);
            
            return true;
        }
        throw new GeneralException(trans('exceptions.backend.salarys.create_error'));
    }

    /**
     * For updating the respective Model in storage
     *
     * @param salary $salary
     * @param  $input
     * @throws GeneralException
     * return bool
     */
    public function update($salary, array $input)
    {
        $data = $input['data'];
        foreach ($data as $key => $val) {
            if (in_array($key, ['start_date'], 1)) 
                $data[$key] = date_for_database($val);
        }
        $salary->update($data);
        // quote line items
        $data_items = $input['data_items'];
        //dd($data_items);
        foreach ($data_items as $item) {
            $item = array_replace($item, [
                'ins' => $data['ins'],
                'user_id' => $data['user_id'],
                'contract_id' => $salary->id
            ]);
            $data_item = AllowanceEmployee::firstOrNew(['id' => $item['id']]);
            $data_item->fill($item);
            if (!$data_item->id) unset($data_item->id);
            $data_item->save();                
        }
        if ($salary) {
            DB::commit();
            return true;
        }

        DB::rollBack();
        throw new GeneralException(trans('exceptions.backend.salarys.update_error'));
    }

    /**
     * For deleting the respective model from storage
     *
     * @param salary $salary
     * @throws GeneralException
     * @return bool
     */
    public function delete(Salary $salary)
    {
        if ($salary->delete()&& $salary->employee_allowance->each->delete()) {
            return true;
        }

        throw new GeneralException(trans('exceptions.backend.salarys.delete_error'));
    }
}
