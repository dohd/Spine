<?php

namespace App\Repositories\Focus\tenant_service;

use App\Exceptions\GeneralException;
use App\Models\tenant_service\TenantService;
use App\Repositories\BaseRepository;
use Carbon\Carbon;

/**
 * Class ProductcategoryRepository.
 */
class TenantServiceRepository extends BaseRepository
{
    /**
     * Associated Repository Model.
     */
    const MODEL = TenantService::class;

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
    public function create(array $input)
    {   
        $input['cost'] = numberClean($input['cost']);
        $input['date'] = date_for_database($input['date']);
        
        $due_date = '';
        $date = new Carbon($input['date']);
        $subscr = $input['subscription'];
        if ($subscr == 'Monthly') $due_date = $date->addMonth()->format('Y-m-d');
        if ($subscr == 'Quarterly') $due_date = $date->addMonths(3)->format('Y-m-d');
        if ($subscr == 'Yearly') $due_date = $date->addYear()->format('Y-m-d');
        $input['due_date'] = $due_date;
 
        $service = TenantService::create($input);
        return $service;
    }

    /**
     * For updating the respective Model in storage
     *
     * @param TenantService $tenant_service
     * @param  $input
     * @throws GeneralException
     * return bool
     */
    public function update(TenantService $tenant_service, array $input)
    {   
        $input['cost'] = numberClean($input['cost']);
        $input['date'] = date_for_database($input['date']);
        
        $due_date = '';
        $date = new Carbon($input['date']);
        $subscr = $input['subscription'];
        if ($subscr == 'Monthly') $due_date = $date->addMonth()->format('Y-m-d');
        if ($subscr == 'Quarterly') $due_date = $date->addMonths(3)->format('Y-m-d');
        if ($subscr == 'Yearly') $due_date = $date->addYear()->format('Y-m-d');
        $input['due_date'] = $due_date;

        return $tenant_service->update($input);
    }

    /**
     * For deleting the respective model from storage
     *
     * @param TenantService $tenant_service
     * @throws GeneralException
     * @return bool
     */
    public function delete(TenantService $tenant_service)
    {
        return $tenant_service->delete();
    }
}
