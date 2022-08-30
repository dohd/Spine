<?php

namespace App\Repositories\Focus\lead;

use App\Models\lead\Lead;
use App\Exceptions\GeneralException;
use App\Repositories\BaseRepository;
use Illuminate\Validation\ValidationException;

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
     * @param \App\Models\Lead $lead
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
     * @param \App\Models\lead\Lead $lead
     * @throws GeneralException
     * @return bool
     */
    public function delete(Lead $lead)
    {
        if ($lead->djcs->count()) 
            throw ValidationException::withMessages(['Ticket is attached to DJC Report!']);

        if ($lead->quotes->count()) 
            throw ValidationException::withMessages(['Ticket is attached to Quote!']);
            
        if ($lead->delete()) return true;
        
        throw new GeneralException(trans('exceptions.backend.productcategories.delete_error'));
    }
}