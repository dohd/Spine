<?php

namespace App\Repositories\Focus\lead;

use App\Models\lead\Lead;
use App\Exceptions\GeneralException;
use App\Models\djc\Djc;
use App\Models\items\Prefix;
use App\Models\quote\Quote;
use App\Repositories\BaseRepository;
use DB;
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
        $q = $this->query();

        return $q->get();
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
        $tid = Lead::max('reference');
        if ($data['reference'] <= $tid) $data['reference'] = $tid+1;

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
        DB::beginTransaction();

        $params = ['customer_id' => @$data['customer_id'], 'branch_id' => @$data['branch_id']];
        if ($lead->quote) $lead->quote->update($params);
        if ($lead->djc) $lead->djc->update($params);

        $result = $lead->update($data);

        if ($result) {
            DB::commit();
            return true;
        }

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
        $prefix = Prefix::where('note', 'lead')->first();
        $tid = gen4tid("{$prefix}-", $lead->reference);

        if ($lead->djcs->count()) 
            throw ValidationException::withMessages(["{$tid} is attached to DJC Report!"]);
        if ($lead->quotes->count()) 
            throw ValidationException::withMessages(["{$tid} is attached to Quote!"]);
            
        if ($lead->delete()) return true;
        
        throw new GeneralException(trans('exceptions.backend.productcategories.delete_error'));
    }
}