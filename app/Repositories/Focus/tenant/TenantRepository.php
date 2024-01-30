<?php

namespace App\Repositories\Focus\tenant;

use App\Exceptions\GeneralException;
use App\Models\Access\Permission\PermissionRole;
use App\Models\Access\User\User;
use App\Models\employee\RoleUser;
use App\Models\hrm\Hrm;
use App\Models\tenant\Tenant;
use App\Models\tenant_package\TenantPackage;
use App\Models\tenant_package\TenantPackageItem;
use App\Repositories\BaseRepository;
use Carbon\Carbon;
use DB;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class TenantRepository extends BaseRepository
{
    /**
     * Associated Repository Model.
     */
    const MODEL = Tenant::class;

    /**
     * This method is used by Table Controller
     * For getting the table data to show in
     * the grid
     * @return mixed
     */
    public function getForDataTable()
    {

        $q = $this->query()->where('id', '>', 1);

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
        DB::beginTransaction();

        $package_data = Arr::only($input, ['customer_id', 'subscr_term', 'date', 'package_id', 'cost', 'maintenance_cost', 'extras_cost', 'total_cost', 'package_item_id']);
        $tenant_data = array_diff_key($input, $package_data);

        $tenant = Tenant::create($tenant_data);

        $package_data = array_replace($package_data, [
            'company_id' => $tenant->id,
            'date' => date_for_database($package_data['date']),
            'cost' => numberClean($package_data['cost']),
            'maintenance_cost' => numberClean($package_data['maintenance_cost']),
            'extras_cost' => numberClean($package_data['extras_cost']),
            'total_cost' => numberClean($package_data['total_cost']),
            'due_date' => (new Carbon(date('Y-m-d')))->addMonths(@$package_data['subscr_term'])->format('Y-m-d'),
        ]);
        unset($package_data['package_item_id']);
        $tenant_package = TenantPackage::create($package_data);

        $input['package_item_id'] = @$input['package_item_id'] ?: [];
        foreach ($input['package_item_id'] as $key => $value) {
            $input['package_item_id'][$key] = [
                'tenant_package_id' => $tenant_package->id,
                'package_item_id' => $value,
            ];
        }
        TenantPackageItem::insert($input['package_item_id']);

        DB::commit();
        return $tenant;
    }

    /**
     * For updating the respective Model in storage
     *
     * @param Productcategory $productcategory
     * @param  $input
     * @throws GeneralException
     * return bool
     */
    public function update(Tenant $tenant, array $input)
    {
        DB::beginTransaction();

        $package_data = Arr::only($input, ['customer_id', 'subscr_term', 'date', 'package_id', 'cost', 'maintenance_cost', 'extras_cost', 'total_cost', 'package_item_id']);
        $tenant_data = array_diff_key($input, $package_data);
        
        $tenant->update($tenant_data);

        $tenant_package = $tenant->package;
        if ($tenant_package) {
            $package_data = array_replace($package_data, [
                'date' => date_for_database($package_data['date']),
                'cost' => numberClean($package_data['cost']),
                'maintenance_cost' => numberClean($package_data['maintenance_cost']),
                'extras_cost' => numberClean($package_data['extras_cost']),
                'total_cost' => numberClean($package_data['total_cost']),
                'due_date' => (new Carbon(date('Y-m-d')))->addMonths(@$package_data['subscr_term'])->format('Y-m-d'),
            ]);
            unset($package_data['package_item_id']);
            $tenant_package->update($package_data);
            $tenant_package->items()->delete();
            $input['package_item_id'] = @$input['package_item_id'] ?: [];
            foreach ($input['package_item_id'] as $key => $value) {
                $input['package_item_id'][$key] = [
                    'tenant_package_id' => $tenant_package->id,
                    'package_item_id' => $value,
                ];
            }
            TenantPackageItem::insert($input['package_item_id']);
        }

        DB::commit();
        return true;
    }

    /**
     * For deleting the respective model from storage
     *
     * @param Productcategory $productcategory
     * @throws GeneralException
     * @return bool
     */
    public function delete(Tenant $tenant)
    {
        DB::beginTransaction();
        $package = $tenant->package;
        if ($package) {
            $package->items()->delete();
            $package()->delete();
        }
        $result = $tenant->delete();
        
        if ($result) {
            DB::commit();
            return true;
        }
    }
}
