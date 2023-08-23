<?php

namespace App\Repositories\Focus\refill_customer;

use App\Exceptions\GeneralException;
use App\Models\refill_customer\RefillCustomer;
use App\Repositories\BaseRepository;

class RefillCustomerRepository extends BaseRepository
{
    /**
     * Associated Repository Model.
     */
    const MODEL = RefillCustomer::class;

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
     * @return RefillCustomer $refill_customer
     */
    public function create(array $input)
    {
        // dd($input);
        foreach ($input as $key => $val) {
            if ($key == 'start_date') $input[$key] = date_for_database($val);
            if (in_array($key, ['qty', 'viable_qty'])) $input[$key] = numberClean($val);
        }
        
        $result = RefillCustomer::create($input);
        if ($result) return $result;
            
        throw new GeneralException(trans('exceptions.backend.leave_category.create_error'));
    }

    /**
     * For updating the respective Model in storage
     *
     * @param RefillCustomer $refill_customer
     * @param  array $input
     * @throws GeneralException
     * return bool
     */
    public function update(RefillCustomer $refill_customer, array $input)
    {
        // dd($input);
        foreach ($input as $key => $val) {
            if ($key == 'start_date') $input[$key] = date_for_database($val);
            if (in_array($key, ['qty', 'viable_qty'])) $input[$key] = numberClean($val);
        }

        if ($refill_customer->update($input)) return $refill_customer;

        throw new GeneralException(trans('exceptions.backend.leave_category.update_error'));
    }

    /**
     * For deleting the respective model from storage
     *
     * @param RefillCustomer $refill_customer
     * @throws GeneralException
     * @return bool
     */
    public function delete(RefillCustomer $refill_customer)
    {
        if ($refill_customer->delete()) return true;
            
        throw new GeneralException(trans('exceptions.backend.leave_category.delete_error'));
    }
}
