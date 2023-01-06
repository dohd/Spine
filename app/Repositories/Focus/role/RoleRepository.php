<?php

namespace App\Repositories\Focus\role;

use App\Exceptions\GeneralException;
use App\Models\Access\Role\Role;
use App\Repositories\BaseRepository;
use Illuminate\Support\Facades\DB;

/**
 * Class RoleRepository.
 */
class RoleRepository extends BaseRepository
{
    /**
     * Associated Repository Model.
     */
    const MODEL = Role::class;

    /**
     * @param string $order_by
     * @param string $sort
     *
     * @return mixed
     */
    public function getAll($order_by = 'sort', $sort = 'asc')
    {
        return $this->query()
            ->with('users', 'permissions')
            ->orderBy($order_by, $sort)
            ->get();
    }

    /**
     * @return mixed
     */
    public function getForDataTable()
    {
        $q = $this->query();
        // $q->where('roles.status', 0);

        $q->leftjoin('role_user', 'role_user.role_id', '=', 'roles.id')
        ->leftjoin('users', 'role_user.user_id', '=', 'users.id')
        ->leftjoin('permission_role', 'permission_role.role_id', '=', 'roles.id')
        ->leftjoin('permissions', 'permission_role.permission_id', '=', 'permissions.id');

        $q->select([
            'roles.id',  'roles.name', 'all',   'roles.sort', 'roles.status',  'roles.created_at', 'roles.updated_at',  'roles.ins',
            DB::raw("GROUP_CONCAT( DISTINCT rose_permissions.display_name SEPARATOR '<br/>') as permission_name"),
            DB::raw('(SELECT COUNT(rose_role_user.id) FROM rose_role_user LEFT JOIN rose_users ON rose_role_user.user_id = rose_users.id WHERE rose_role_user.role_id = rose_roles.id AND rose_users.deleted_at IS NULL) AS userCount'),
        ])
        ->groupBy(config('access.roles_table') . '.id', config('access.roles_table') . '.name', config('access.roles_table') . '.all', config('access.roles_table') . '.sort');

        return $q;
    }

    /**
     * @param array $input
     *
     * @throws GeneralException
     *
     * @return bool
     */
    public function create(array $input)
    {
        // dd($input);
        DB::beginTransaction();

        $role_exists = $this->query()->where('name', $input['name'])->first();
        if ($role_exists) throw new GeneralException(trans('exceptions.backend.access.roles.already_exists'));

        if (!isset($input['permissions'])) $input['permissions'] = [];

        $has_all_rights = ($input['associated_permissions'] == 'all') ? true : false;
        if (!$has_all_rights) {
            // check if the role must contain a permission as per config
            if (config('access.roles.role_must_contain_permission') && !$input['permissions'])
                throw new GeneralException(trans('exceptions.backend.access.roles.needs_permission'));
        }

        $role = new Role;
        $role->fill([
            'name' => $input['name'],
            'sort' => 0,
            'all' => 0,
            'status' => 0,
        ]);
        if (isset($input['sort'])) {
            $input['sort'] = numberClean($input['sort']);
            if ($input['sort'] > 0) $role->sort = $input['sort'];
        }
        if ($role->save()) {
            if ($input['permissions']) {
                $permissions = array_filter($input['permissions'], fn ($v) => is_numeric($v));
                $role->attachPermissions($permissions);
            }

            DB::commit();
            return $role;
        }

        throw new GeneralException(trans('exceptions.backend.access.roles.create_error'));
    }

    /**
     * @param Model $role
     * @param  $input
     *
     * @throws GeneralException
     *
     * @return bool
     */
    public function update($role, array $input)
    {
        // dd($input);
        DB::beginTransaction();

        if (!isset($input['permissions'])) $input['permissions'] = [];

        // cannot update system set roles
        if (!$role->ins || in_array($role->id, [1,2])) 
            throw new GeneralException(trans('exceptions.backend.access.roles.update_error'));
            
        $has_all_rights = ($input['associated_permissions'] == 'all') ? true : false;
        if (!$has_all_rights) {
            // check if the role must contain a permission as per config
            if (config('access.roles.role_must_contain_permission') && !$input['permissions']) 
                throw new GeneralException(trans('exceptions.backend.access.roles.needs_permission'));
        }

        $role->fill([
            'name' => $input['name'],
            'sort' => 0,
            'all' => 0,
            'status' => (isset($input['status']) && $input['status'] == 1) ? 1 : 0,
        ]);
        if (isset($input['sort'])) {
            $input['sort'] = numberClean($input['sort']);
            if ($input['sort'] > 0) $role->sort = $input['sort'];
        }
        if ($role->save()) {
            // clear previous permissions
            $role->permissions()->sync([]);
            if (!$has_all_rights) {
                if ($input['permissions']) {
                    $permissions = array_filter($input['permissions'], fn ($v) => is_numeric($v));
                    $role->attachPermissions($permissions);
                }
            } 

            DB::commit();
            return $role;
        }

        throw new GeneralException(trans('exceptions.backend.access.roles.update_error'));
    }

    /**
     * @param Role $role
     *
     * @throws GeneralException
     *
     * @return bool
     */
    public function delete(Role $role)
    {
        DB::beginTransaction();

        // cannot delete sytem set roles
        if (in_array($role->id, [1,2])) throw new GeneralException(trans('exceptions.backend.access.roles.cant_delete_admin'));
        // cannot delete user associated role
        if ($role->users()->count()) throw new GeneralException(trans('exceptions.backend.access.roles.has_users'));

        $role->permissions()->sync([]);
        if ($role->delete()) {
            DB::commit();
            return true;
        }
            
        throw new GeneralException(trans('exceptions.backend.access.roles.delete_error'));
    }

    /**
     * @return mixed
     */
    public function getDefaultUserRole()
    {
        $q = $this->query();
        if (is_numeric(config('access.users.default_role')))
            return $q->where('id', (int) config('access.users.default_role'))->first();
        
        return $q->where('name', config('access.users.default_role'))->first();
    }
}
