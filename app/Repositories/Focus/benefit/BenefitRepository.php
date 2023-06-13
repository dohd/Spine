<?php

namespace App\Repositories\Focus\benefit;

use DB;
use Carbon\Carbon;
use App\Models\benefit\Benefit;
use App\Exceptions\GeneralException;
use App\Repositories\BaseRepository;
use Illuminate\Database\Eloquent\Model;

/**
 * Class benefitRepository.
 */
class BenefitRepository extends BaseRepository
{
    /**
     * Associated Repository Model.
     */
    const MODEL = Benefit::class;

    /**
     * This method is used by Table Controller
     * For getting the table data to show in
     * the grid
     * @return mixed
     */
    public function getForDataTable()
    {

        return $this->query()
            ->get(['id','name','type','employee_id','employee_name','leave_payment_days','amount','note','created_at']);
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
        $benefit = $input;
        foreach ($benefit as $key => $val) {
            // $rate_keys = [
            //     'employee_id','employee_name','issue_date','return_date','note','total_cost'
            // ];
            if (in_array($key, ['month'], 1))
                $benefit[$key] = date_for_database($val);
            
        }
        $input = array_map( 'strip_tags', $benefit);
        if (Benefit::create($input)) {
            return true;
        }
        throw new GeneralException(trans('exceptions.backend.benefits.create_error'));
    }

    /**
     * For updating the respective Model in storage
     *
     * @param benefit $benefit
     * @param  $input
     * @throws GeneralException
     * return bool
     */
    public function update(Benefit $benefit, array $input)
    {
        $input = array_map( 'strip_tags', $input);
    	if ($benefit->update($input))
            return true;

        throw new GeneralException(trans('exceptions.backend.benefits.update_error'));
    }

    /**
     * For deleting the respective model from storage
     *
     * @param benefit $benefit
     * @throws GeneralException
     * @return bool
     */
    public function delete(Benefit $benefit)
    {
        if ($benefit->delete()) {
            return true;
        }

        throw new GeneralException(trans('exceptions.backend.benefits.delete_error'));
    }
}
