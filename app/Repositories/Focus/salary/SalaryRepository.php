<?php

namespace App\Repositories\Focus\salary;

use DB;
use Carbon\Carbon;
use App\Models\salary\Salary;
use App\Exceptions\GeneralException;
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
    public function create(array $input)
    {
        //dd($input);
        $salary = $input;
        foreach ($salary as $key => $val) {
            // $rate_keys = [
            //     'employee_id','employee_name','issue_date','return_date','note','total_cost'
            // ];
            if (in_array($key, ['month'], 1))
                $salary[$key] = date_for_database($val);
            
        }
        $input = array_map( 'strip_tags', $salary);
        if (Salary::create($input)) {
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
    public function update(Salary $salary, array $input)
    {
        $input = array_map( 'strip_tags', $input);
    	if ($salary->update($input))
            return true;

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
        if ($salary->delete()) {
            return true;
        }

        throw new GeneralException(trans('exceptions.backend.salarys.delete_error'));
    }
}
