<?php

namespace App\Repositories\Focus\equipment;

use App\Models\equipment\Equipment;
use App\Exceptions\GeneralException;
use App\Repositories\BaseRepository;

/**
 * Class ProductcategoryRepository.
 */
class EquipmentRepository extends BaseRepository
{
    /**
     * Associated Repository Model.
     */
    const MODEL = Equipment::class;

    /**
     * This method is used by Table Controller
     * For getting the table data to show in
     * the grid
     * @return mixed
     */
    public function getForDataTable()
    {
        $q = $this->query();

        $q->when(request('customer_id'), function ($q) {
            $q->where('customer_id', request('customer_id'));
        })->when(request('branch_id'), function ($q) {
            $q->where('branch_id', request('branch_id'));
        });

        printlog($this->query()->find(1));
            
        return $q->get();
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
        foreach ($input as $key => $val) {
            if ($key == 'install_date') $input[$key] = date_for_database($val);
            if ($key == 'service_rate') $input[$key] = numberClean($val);
        }

        $result = Equipment::create($input);
        if ($result) return $result;

        throw new GeneralException('Error Creating Equipment');
    }

    /**
     * For updating the respective Model in storage
     *
     * @param Productcategory $productcategory
     * @param  $input
     * @throws GeneralException
     * return bool
     */
    public function update(Equipment $equipment, array $input)
    {
        // dd($input);
        foreach ($input as $key => $val) {
            if ($key == 'install_date') $input[$key] = date_for_database($val);
            if ($key == 'service_rate') $input[$key] = numberClean($val);
        }
        
        if ($equipment->update($input)) return true;
            
        throw new GeneralException(trans('exceptions.backend.productcategories.update_error'));
    }

    /**
     * For deleting the respective model from storage
     *
     * @param Productcategory $productcategory
     * @throws GeneralException
     * @return bool
     */
    public function delete($equipment)
    {
        if ($equipment->delete()) return true;
            
        throw new GeneralException(trans('exceptions.backend.productcategories.delete_error'));
    }
}
