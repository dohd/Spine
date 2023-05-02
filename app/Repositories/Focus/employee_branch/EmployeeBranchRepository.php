<?php

namespace App\Repositories\Focus\employee_branch;

use DB;
use Carbon\Carbon;
use App\Models\employee_branch\EmployeeBranch;
use App\Exceptions\GeneralException;
use App\Repositories\BaseRepository;
use Illuminate\Database\Eloquent\Model;

/**
 * Class EmployeeBranchRepository.
 */
class EmployeeBranchRepository extends BaseRepository
{
    /**
     * Associated Repository Model.
     */
    const MODEL = EmployeeBranch::class;

    /**
     * This method is used by Table Controller
     * For getting the table data to show in
     * the grid
     * @return mixed
     */
    public function getForDataTable()
    {

        return $this->query()
            ->get();
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
        $input = array_map( 'strip_tags', $input);
        if (EmployeeBranch::create($input)) {
            return true;
        }
        throw new GeneralException(trans('exceptions.backend.employee_branch.create_error'));
    }

    /**
     * For updating the respective Model in storage
     *
     * @param EmployeeBranch $EmployeeBranch
     * @param  $input
     * @throws GeneralException
     * return bool
     */
    public function update(EmployeeBranch $employee_branch, array $input)
    {
        $input = array_map( 'strip_tags', $input);
    	if ($employee_branch->update($input))
            return true;

        throw new GeneralException(trans('exceptions.backend.employee_branch.update_error'));
    }

    /**
     * For deleting the respective model from storage
     *
     * @param EmployeeBranch $EmployeeBranch
     * @throws GeneralException
     * @return bool
     */
    public function delete(EmployeeBranch $employee_branch)
    {
        if ($employee_branch->delete()) {
            return true;
        }

        throw new GeneralException(trans('exceptions.backend.employee_branch.delete_error'));
    }
}
