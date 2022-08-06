<?php

namespace App\Repositories\Focus\productvariable;

use App\Models\productvariable\Productvariable;
use App\Exceptions\GeneralException;
use App\Repositories\BaseRepository;

/**
 * Class ProductvariableRepository.
 */
class ProductvariableRepository extends BaseRepository
{
    /**
     * Associated Repository Model.
     */
    const MODEL = Productvariable::class;

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
        $input['ins'] = auth()->user()->ins;

        $result = Productvariable::create($input);
        if ($result) return $result;

        throw new GeneralException(trans('exceptions.backend.productvariables.create_error'));
    }

    /**
     * For updating the respective Model in storage
     *
     * @param App\Models\productvariable\Productvariable $productvariable
     * @param  array $input
     * @throws GeneralException
     * @return bool
     */
    public function update($productvariable, array $input)
    {
    	if ($productvariable->update($input))  return true;

        throw new GeneralException(trans('exceptions.backend.productvariables.update_error'));
    }

    /**
     * For deleting the respective model from storage
     *
     * @param Productvariable $productvariable
     * @throws GeneralException
     * @return bool
     */
    public function delete($productvariable)
    {
        if ($productvariable->delete()) return true;

        throw new GeneralException(trans('exceptions.backend.productvariables.delete_error'));
    }
}
