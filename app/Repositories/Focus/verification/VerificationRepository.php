<?php

namespace App\Repositories\Focus\verification;

use App\Exceptions\GeneralException;
use App\Models\quote\Quote;
use App\Models\verification\Verification;
use App\Repositories\BaseRepository;

class VerificationRepository extends BaseRepository
{
    /**
     * Associated Repository Model.
     */
    const MODEL = Verification::class;

    /**
     * This method is used by Table Controller
     * For getting the table data to show in
     * the grid
     * @return mixed
     */
    public function getForDataTable()
    {
        $q = $this->query();
            
        return $q->get();
    }

    public function getForVerificationQuoteDataTable()
    {
        $q = Quote::query();
            
        return $q->get();
    }

    /**
     * For Creating the respective model in storage
     *
     * @param array $input
     * @throws GeneralException
     * @return Verification $verification
     */
    public function create(array $input)
    {
        dd($input);
            
        throw new GeneralException(trans('exceptions.backend.leave_category.create_error'));
    }

    /**
     * For updating the respective Model in storage
     *
     * @param Verification $verification
     * @param  array $input
     * @throws GeneralException
     * return bool
     */
    public function update(Verification $verification, array $input)
    {
        dd($input);

        throw new GeneralException(trans('exceptions.backend.leave_category.update_error'));
    }

    /**
     * For deleting the respective model from storage
     *
     * @param Verification $verification
     * @throws GeneralException
     * @return bool
     */
    public function delete(Verification $verification)
    {
        dd($verification->id);
        
        if ($verification->delete()) return true;
            
        throw new GeneralException(trans('exceptions.backend.leave_category.delete_error'));
    }
}
