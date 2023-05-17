<?php

namespace App\Repositories\Focus\overtimepay;

use DB;
use Carbon\Carbon;
use App\Models\overtimepay\OvertimePay;
use App\Exceptions\GeneralException;
use App\Repositories\BaseRepository;
use Illuminate\Database\Eloquent\Model;

/**
 * Class overtimepayRepository.
 */
class OvertimePayRepository extends BaseRepository
{
    /**
     * Associated Repository Model.
     */
    const MODEL = OvertimePay::class;

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
        if (OvertimePay::create($input)) {
            return true;
        }
        throw new GeneralException(trans('exceptions.backend.overtimepays.create_error'));
    }

    /**
     * For updating the respective Model in storage
     *
     * @param overtimepay $overtimepay
     * @param  $input
     * @throws GeneralException
     * return bool
     */
    public function update(OvertimePay $overtimepay, array $input)
    {
        $input = array_map( 'strip_tags', $input);
    	if ($overtimepay->update($input))
            return true;

        throw new GeneralException(trans('exceptions.backend.overtimepays.update_error'));
    }

    /**
     * For deleting the respective model from storage
     *
     * @param overtimepay $overtimepay
     * @throws GeneralException
     * @return bool
     */
    public function delete(OvertimePay $overtimepay)
    {
        if ($overtimepay->delete()) {
            return true;
        }

        throw new GeneralException(trans('exceptions.backend.overtimepays.delete_error'));
    }
}
