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

        if (request('customer_id') && request('branch_id')) {
            $q->where([
                'customer_id' => request('customer_id'),
                'branch_id' => request('branch_id')
            ]);
        } elseif (request('customer_id')) {
            $q->where('customer_id', request('customer_id'));
        } else return $q->limit(100)->get();
            
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
        $input['service_rate'] = numberClean($input['service_rate']);
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
        $input['service_rate'] = numberClean($input['service_rate']);
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
