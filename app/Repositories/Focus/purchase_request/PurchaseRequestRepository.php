<?php

namespace App\Repositories\Focus\purchase_request;

use App\Exceptions\GeneralException;
use App\Models\purchase_request\PurchaseRequest;
use App\Repositories\BaseRepository;

class PurchaseRequestRepository extends BaseRepository
{
    /**
     * Associated Repository Model.
     */
    const MODEL = PurchaseRequest::class;

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
     * @return PurchaseRequest $purchase_request
     */
    public function create(array $input)
    {
        // dd($input);
        foreach ($input as $key => $val) {
            if (in_array($key, ['date', 'expect_date'])) 
                $input[$key] = date_for_database($val);
        }

        $tid = PurchaseRequest::where('ins', auth()->user()->ins)->max('tid');
        if ($input['tid'] <= $tid) $input['tid'] = $tid+1;
        
        $result = PurchaseRequest::create($input);
        if ($result) return $result;
            
        throw new GeneralException(trans('exceptions.backend.leave_category.create_error'));
    }

    /**
     * For updating the respective Model in storage
     *
     * @param PurchaseRequest $purchase_request
     * @param  array $input
     * @throws GeneralException
     * return bool
     */
    public function update(PurchaseRequest $purchase_request, array $input)
    {
        dd($input);
        foreach ($input as $key => $val) {
            if (in_array($key, ['date', 'expect_date'])) 
                $input[$key] = date_for_database($val);
        }

        if ($purchase_request->update($input)) return $purchase_request;

        throw new GeneralException(trans('exceptions.backend.leave_category.update_error'));
    }

    /**
     * For deleting the respective model from storage
     *
     * @param PurchaseRequest $purchase_request
     * @throws GeneralException
     * @return bool
     */
    public function delete(PurchaseRequest $purchase_request)
    {
        if ($purchase_request->delete()) return true;
            
        throw new GeneralException(trans('exceptions.backend.leave_category.delete_error'));
    }
}
