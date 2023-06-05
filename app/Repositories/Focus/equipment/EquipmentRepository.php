<?php

namespace App\Repositories\Focus\equipment;

use App\Models\equipment\Equipment;
use App\Exceptions\GeneralException;
use App\Repositories\BaseRepository;
use Illuminate\Validation\ValidationException;

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
        })->when(request('schedule_id'), function ($q) {
            // fetch schedule equipments
            $q->whereHas('contract_equipments', function($q) {
                $q->where('schedule_id', request('schedule_id'));
            });
        })->when(request('is_serviced') == '0', function ($q) {
            // fetch unserviced equipments
            $q->whereHas('contract_equipments', function($q) {
                $q->where('schedule_id', request('schedule_id'));
            })->where(function ($q) {
                $q->doesntHave('contract_service_items', 'or', function ($q) {
                    $q->whereHas('contractservice', function ($q) {
                        $q->where('schedule_id', request('schedule_id'));
                    });
                });
            });
        });
        
        
        $q->with(['customer', 'branch']);
        return $q;
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
        $service = $equipment->contract_service;
        if ($service) throw ValidationException::withMessages(["Equipment is attached to a report! Jobcard No. {$service->jobcard_no}"]);
            
        if ($equipment->delete()) return true;
    }
}
