<?php

namespace App\Repositories\Focus\advance_payment;

use App\Exceptions\GeneralException;
use App\Models\advance_payment\AdvancePayment;
use App\Repositories\BaseRepository;

class AdvancePaymentRepository extends BaseRepository
{
    /**
     * Associated Repository Model.
     */
    const MODEL = AdvancePayment::class;

    /**
     * This method is used by Table Controller
     * For getting the table data to show in
     * the grid
     * @return mixed
     */
    public function getForDataTable()
    {
        $q = $this->query();

        if (!access()->allow('department-manage'))
            $q->where('employee_id', auth()->user()->id);
            
        return $q->get();
    }

    /**
     * For Creating the respective model in storage
     *
     * @param array $input
     * @throws GeneralException
     * @return AdvancePayment $advance_payment
     */
    public function create(array $input)
    {
        dd($input);
        foreach ($input as $key => $val) {
            if ($key == 'start_date') $input[$key] = date_for_database($val);
            if (in_array($key, ['qty', 'viable_qty'])) $input[$key] = numberClean($val);
        }
        
        $result = AdvancePayment::create($input);
        if ($result) return $result;
            
        throw new GeneralException(trans('exceptions.backend.advance_payment.create_error'));
    }

    /**
     * For updating the respective Model in storage
     *
     * @param AdvancePayment $advance_payment
     * @param  array $input
     * @throws GeneralException
     * return bool
     */
    public function update(AdvancePayment $advance_payment, array $input)
    {
        dd($input);
        foreach ($input as $key => $val) {
            if ($key == 'start_date') $input[$key] = date_for_database($val);
            if (in_array($key, ['qty', 'viable_qty'])) $input[$key] = numberClean($val);
        }

        if ($advance_payment->update($input)) return $advance_payment;

        throw new GeneralException(trans('exceptions.backend.advance_payment.update_error'));
    }

    /**
     * For deleting the respective model from storage
     *
     * @param AdvancePayment $advance_payment
     * @throws GeneralException
     * @return bool
     */
    public function delete(AdvancePayment $advance_payment)
    {
        if ($advance_payment->delete()) return true;
            
        throw new GeneralException(trans('exceptions.backend.advance_payment.delete_error'));
    }
}
