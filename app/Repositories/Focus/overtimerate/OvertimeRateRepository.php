<?php

namespace App\Repositories\Focus\overtimerate;

use DB;
use Carbon\Carbon;
use App\Models\overtimerate\OvertimeRate;
use App\Exceptions\GeneralException;
use App\Repositories\BaseRepository;
use Illuminate\Database\Eloquent\Model;

/**
 * Class overtimerateRepository.
 */
class OvertimeRateRepository extends BaseRepository
{
    /**
     * Associated Repository Model.
     */
    const MODEL = OvertimeRate::class;

    /**
     * This method is used by Table Controller
     * For getting the table data to show in
     * the grid
     * @return mixed
     */
    public function getForDataTable()
    {

        return $this->query()
            ->get(['id','name','rate_option','rate','created_at']);
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
        if (OvertimeRate::create($input)) {
            return true;
        }
        throw new GeneralException(trans('exceptions.backend.overtimerates.create_error'));
    }

    /**
     * For updating the respective Model in storage
     *
     * @param overtimerate $overtimerate
     * @param  $input
     * @throws GeneralException
     * return bool
     */
    public function update(OvertimeRate $overtimerate, array $input)
    {
        $input = array_map( 'strip_tags', $input);
    	if ($overtimerate->update($input))
            return true;

        throw new GeneralException(trans('exceptions.backend.overtimerates.update_error'));
    }

    /**
     * For deleting the respective model from storage
     *
     * @param overtimerate $overtimerate
     * @throws GeneralException
     * @return bool
     */
    public function delete(OvertimeRate $overtimerate)
    {
        if ($overtimerate->delete()) {
            return true;
        }

        throw new GeneralException(trans('exceptions.backend.overtimerates.delete_error'));
    }
}
