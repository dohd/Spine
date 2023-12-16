<?php

namespace App\Repositories\Focus\tenant;

use App\Exceptions\GeneralException;
use App\Models\Access\Permission\PermissionRole;
use App\Models\Access\User\User;
use App\Models\employee\RoleUser;
use App\Models\hrm\Hrm;
use App\Models\tenant\Tenant;
use App\Repositories\BaseRepository;
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

        // save images
        //

        $user_data = Arr::only($input, ['first_name', 'last_name', 'email', 'password']);
        $tenant_data = array_diff_key($input, $user_data);
        $tenant_data['email'] = $tenant_data['cemail'];
        unset($tenant_data['cemail']);
        $tenant = Tenant::create($tenant_data);

        $user_data = array_replace($user_data, [
            'username' => Str::random(4),
            'confirmed' => 1,
            'ins' => $tenant->id,
            'created_by' => auth()->user()->id,
        ]);
        $hrm = Hrm::create($user_data);

        // assign business owner role and permissions
        RoleUser::create(['user_id' => $hrm->id, 'role_id' => 2]);
        $permissions = PermissionRole::select('permission_id')->distinct()->where('role_id', 2)->pluck('permission_id')->toArray();
        $hrm->permissions()->attach($permissions);

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

        // save images
        //

        $user_data = Arr::only($input, ['first_name', 'last_name', 'email', 'password']);
        $tenant_data = array_diff_key($input, $user_data);
        $tenant_data['email'] = $tenant_data['cemail'];
        unset($tenant_data['cemail']);
        $tenant->update($tenant_data);

        $hrm = User::where('ins', $tenant->id)->where('created_at', $tenant->created_at)->first();
        if ($hrm) {
            if (empty($user_data['password'])) unset($user_data['password']);
            $user_data['updated_by'] = auth()->user()->id;
            $hrm->update($user_data);
        } else {
            $user_data = array_replace($user_data, [
                'username' => Str::random(4),
                'confirmed' => 1,
                'ins' => $tenant->id,
                'created_by' => auth()->user()->id,
                'updated_by' => auth()->user()->id,
            ]);
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
