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
