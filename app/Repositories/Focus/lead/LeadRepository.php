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
    public function create(array $data)
    {
        $data['date_of_request'] = date_for_database($data['date_of_request']);
        // increament reference
        $lead = Lead::orderBy('reference', 'desc')->first('reference');
        if ($lead && $data['reference'] <= $lead->reference) {
            $data['reference'] = $lead->reference + 1;
        }

        $result = Lead::create($data);
        return $result;

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
    public function update(Lead $lead, array $data)
    {
        $data = array_map('strip_tags', $data);
        if ($lead->update($data)) return true;

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
        if (count($lead->quotes) || count($lead->djcs)) return;          
        else return $lead->delete();
        
        throw new GeneralException(trans('exceptions.backend.productcategories.delete_error'));
    }
}
