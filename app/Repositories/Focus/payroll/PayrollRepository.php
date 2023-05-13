<?php

namespace App\Repositories\Focus\payroll;

use DB;
use Carbon\Carbon;
use App\Models\payroll\Payroll;
use App\Exceptions\GeneralException;
use App\Repositories\BaseRepository;
use Illuminate\Database\Eloquent\Model;

/**
 * Class payrollRepository.
 */
class PayrollRepository extends BaseRepository
{
    /**
     * Associated Repository Model.
     */
    const MODEL = Payroll::class;

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
        if (Payroll::create($input)) {
            return true;
        }
        throw new GeneralException(trans('exceptions.backend.payrolls.create_error'));
    }

    /**
     * For updating the respective Model in storage
     *
     * @param payroll $payroll
     * @param  $input
     * @throws GeneralException
     * return bool
     */
    public function update(Payroll $payroll, array $input)
    {
        $input = array_map( 'strip_tags', $input);
    	if ($payroll->update($input))
            return true;

        throw new GeneralException(trans('exceptions.backend.payrolls.update_error'));
    }

    /**
     * For deleting the respective model from storage
     *
     * @param payroll $payroll
     * @throws GeneralException
     * @return bool
     */
    public function delete(Payroll $payroll)
    {
        if ($payroll->delete()) {
            return true;
        }

        throw new GeneralException(trans('exceptions.backend.payrolls.delete_error'));
    }
}
