<?php

namespace App\Repositories\Focus\lead;

use App\Models\lead\Lead;
use App\Exceptions\GeneralException;
use App\Repositories\BaseRepository;

/**
 * Class ProductcategoryRepository.
 */
class LeadRepository extends BaseRepository
{
    /**
     * Associated Repository Model.
     */
    const MODEL = Lead::class;

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
        $input['date_of_request'] = date_for_database($input['date_of_request']);
        // increament reference
        $tid = Lead::orderBy('reference', 'desc')->first('reference')->reference;
        if ($input['reference'] <= $tid) {
            $input['reference'] = $tid + 1;
        }

        $result = Lead::create($input);

        if ($result) return $result;

        throw new GeneralException('Error Creating Lead');
    }

    /**
     * For updating the respective Model in storage
     *
     * @param Productcategory $productcategory
     * @param  $input
     * @throws GeneralException
     * return bool
     */
    public function update(Lead $lead, array $input)
    {
        $input = array_map('strip_tags', $input);
        if ($lead->update($input))
            return true;

        throw new GeneralException(trans('exceptions.backend.productcategories.update_error'));
    }

    /**
     * For deleting the respective model from storage
     *
     * @param Productcategory $productcategory
     * @throws GeneralException
     * @return bool
     */
    public function delete(Lead $lead)
    {
        if ($lead->delete()) {
            return true;
        }

        throw new GeneralException(trans('exceptions.backend.productcategories.delete_error'));
    }
}
