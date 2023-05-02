<?php

namespace App\Repositories\Focus\leave;

use App\Exceptions\GeneralException;
use App\Models\leave\Leave;
use App\Repositories\BaseRepository;

class LeaveRepository extends BaseRepository
{
    /**
     * Associated Repository Model.
     */
    const MODEL = Leave::class;

    /**
     * This method is used by Table Controller
     * For getting the table data to show in
     * the grid
     * @return mixed
     */
    public function getForDataTable()
    {
        $q = $this->query();

        if (!access()->allow('department-manage'))
            $q->where('employee_id', auth()->user()->id);
            
        return $q->get();
    }

    /**
     * For Creating the respective model in storage
     *
     * @param array $input
     * @throws GeneralException
     * @return Leave $leave
     */
    public function create(array $input)
    {
        // dd($input);
        foreach ($input as $key => $val) {
            if ($key == 'start_date') $input[$key] = date_for_database($val);
            if (in_array($key, ['qty', 'viable_qty'])) $input[$key] = numberClean($val);
        }
        
        $result = Leave::create($input);
        if ($result) return $result;
            
        throw new GeneralException(trans('exceptions.backend.leave_category.create_error'));
    }

    /**
     * For updating the respective Model in storage
     *
     * @param Leave $leave
     * @param  array $input
     * @throws GeneralException
     * return bool
     */
    public function update(Leave $leave, array $input)
    {
        // dd($input);
        foreach ($input as $key => $val) {
            if ($key == 'start_date') $input[$key] = date_for_database($val);
            if (in_array($key, ['qty', 'viable_qty'])) $input[$key] = numberClean($val);
        }

        if ($leave->update($input)) return $leave;

        throw new GeneralException(trans('exceptions.backend.leave_category.update_error'));
    }

    /**
     * For deleting the respective model from storage
     *
     * @param Leave $leave
     * @throws GeneralException
     * @return bool
     */
    public function delete(Leave $leave)
    {
        if ($leave->delete()) return true;
            
        throw new GeneralException(trans('exceptions.backend.leave_category.delete_error'));
    }
}
