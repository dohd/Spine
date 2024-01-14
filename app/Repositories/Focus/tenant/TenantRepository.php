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

        $q = $this->query();
        $q->where('id', '>', 1);

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

        $user_data = Arr::only($input, ['first_name', 'last_name', 'user_email', 'password', 'confirm_password']);
        $package_data = Arr::only($input, ['date', 'package_id', 'cost', 'maintenance_cost', 'extras_cost', 'total_cost', 'package_item_id']);
        $tenant_data = array_diff_key($input, array_merge($user_data, $package_data));

        $tenant = Tenant::create($tenant_data);

        $package_data = array_replace($package_data, [
            'company_id' => $tenant->id,
            'date' => date_for_database($package_data['date']),
            'cost' => numberClean($package_data['cost']),
            'maintenance_cost' => numberClean($package_data['maintenance_cost']),
            'extras_cost' => numberClean($package_data['extras_cost']),
            'total_cost' => numberClean($package_data['total_cost']),
            'date' => date('Y-m-d'),
            'due_date' => (new Carbon(date('Y-m-d')))->addYear()->format('Y-m-d'),
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

        $user_data = array_replace($user_data, [
            'email' => $user_data['user_email'],
            'username' => Str::random(4),
            'confirmed' => 1,
            'ins' => $tenant->id,
            'created_by' => auth()->user()->id,
        ]);
        unset($user_data['user_email'],$user_data['confirm_password']);
        $hrm = Hrm::create($user_data);
        // assign business owner role and permissions
        RoleUser::create(['user_id' => $hrm->id, 'role_id' => 2]);
        $permissions = PermissionRole::select('permission_id')->distinct()->where('role_id', 2)->pluck('permission_id');
        $hrm->permissions()->attach($permissions->toArray());

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

        $user_data = Arr::only($input, ['first_name', 'last_name', 'user_email', 'password', 'confirm_password']);
        $package_data = Arr::only($input, ['date', 'package_id', 'cost', 'maintenance_cost', 'extras_cost', 'total_cost', 'package_item_id']);
        $tenant_data = array_diff_key($input, array_merge($user_data, $package_data));
        
        $tenant->update($tenant_data);

        $tenant_package = $tenant->package;
        if ($tenant_package) {
            $package_data = array_replace($package_data, [
                'date' => date_for_database($package_data['date']),
                'cost' => numberClean($package_data['cost']),
                'maintenance_cost' => numberClean($package_data['maintenance_cost']),
                'extras_cost' => numberClean($package_data['extras_cost']),
                'total_cost' => numberClean($package_data['total_cost']),
                'date' => date('Y-m-d'),
                'due_date' => (new Carbon(date('Y-m-d')))->addYear()->format('Y-m-d'),
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

        $hrm = User::where('ins', $tenant->id)->where('created_at', $tenant->created_at)->first();
        if ($hrm) {
            if (empty($user_data['password'])) unset($user_data['password']);
            $user_data['updated_by'] = auth()->user()->id;
            $hrm->update($user_data);
        } else {
            $user_data = array_replace($user_data, [
                'email' => $user_data['user_email'],
                'username' => Str::random(4),
                'confirmed' => 1,
                'ins' => $tenant->id,
                'created_by' => auth()->user()->id,
                'updated_by' => auth()->user()->id,
            ]);
            unset($user_data['user_email'],$user_data['confirm_password']);
            $hrm = Hrm::create($user_data);
        }
        
        // assign business owner role and permissions
        RoleUser::create(['user_id' => $hrm->id, 'role_id' => 2]);
        $permissions = PermissionRole::select('permission_id')->distinct()->where('role_id', 2)->pluck('permission_id')->toArray();
        $hrm->permissions()->sync($permissions);

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
        return $tenant->delete();
    }
}
