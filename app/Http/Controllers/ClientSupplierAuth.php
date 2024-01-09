<?php

namespace App\Http\Controllers;

use App\Models\Access\Permission\Permission;
use App\Models\Access\Permission\PermissionUser;
use App\Models\hrm\Hrm;

trait ClientSupplierAuth
{
    public function createAuth($entity, $input, $user_type)
    {
        if (isset($input['picture'])) {
            $input['picture'] = $this->uploadAuthImage($input['picture']);
        }
        if ($user_type == 'client_vendor') {
            $user = Hrm::create([
                'client_vendor_id' => $entity->id,
                'first_name' => $input['first_name'],
                'last_name' => $input['last_name'],
                'email' => $input['email'],
                'password' => $input['password'],
                'confirmed' => 1,
                'ins' => auth()->user()->ins,
                'created_by' => auth()->user()->id,
            ]);
        } else {
            $user = Hrm::create([
                'customer_id' => ($user_type == 'client'? $entity->id : null),
                'supplier_id' => ($user_type == 'supplier'? $entity->id : null),
                'first_name' => $input['name'],
                'email' => $input['email'],
                'picture' => $input['picture'],
                'password' => $input['password'],
                'confirmed' => 1,
                'ins' => auth()->user()->ins,
                'created_by' => auth()->user()->id,
            ]);
        }

        // assign permissions
        $perm_ids = [];
        if ($user->customer_id) {
            $perm_ids = Permission::whereIn('name', ['crm', 'manage-client', 'maintenance-project', 'manage-project'])
            ->pluck('id')->toArray();
        } elseif ($user->supplier_id) {
            $perm_ids = Permission::whereIn('name', ['finance', 'manage-supplier', 'stock', 'manage-grn'])
            ->pluck('id')->toArray();
        } elseif ($user->client_vendor_id) {
            $perm_ids = Permission::whereIn('name', ['crm'])
                ->pluck('id')->toArray(); 
        }
        foreach ($perm_ids as $key => $value) {
            $perm_ids[$key] = ['permission_id' => $value, 'user_id' => $user->id];
        }
        PermissionUser::insert($perm_ids);

        return $user;
    }

    public function updateAuth($entity, $input, $user_type)
    {
        $user = Hrm::query()
            ->when($user_type == 'client_vendor', fn($q) => $q->where('client_vendor_id', $entity->id))
            ->when($user_type == 'client', fn($q) => $q->where('customer_id', $entity->id))
            ->when($user_type == 'supplier', fn($q) => $q->where('supplier_id', $entity->id));
        $user = $user->first();   
        if (!$user) return $this->createAuth($entity, $input, $user_type);
        
        if (isset($input['picture'])) {
            $this->removeAuthImage($user);
            $input['picture'] = $this->uploadAuthImage($input['picture']);
        }
        if ($user_type == 'client_vendor') {
            $user->update([
                'client_vendor_id' => $entity->id,
                'first_name' => $input['first_name'],
                'last_name' => $input['last_name'],
                'email' => $input['email'],
                'password' => $input['password'],
                'confirmed' => 1,
                'ins' => auth()->user()->ins,
                'updated_by' => auth()->user()->id,
            ]);
        } else {
            $user->update([
                'customer_id' => $user_type == 'client'? $entity->id : null,
                'supplier_id' => $user_type == 'supplier'? $entity->id : null,
                'first_name' => $input['name'],
                'email' => $input['email'],
                'picture' => $input['picture'],
                'password' => $input['password'],
                'confirmed' => 1,
                'ins' => auth()->user()->ins,
                'updated_by' => auth()->user()->id,
            ]);
        }

        // assign permissions
        $perm_ids = [];
        if ($user->customer_id) {
            $perm_ids = Permission::whereIn('name', ['crm', 'manage-client', 'maintenance-project', 'manage-project'])
                ->pluck('id')->toArray();
        } elseif ($user->supplier_id) {
            $perm_ids = Permission::whereIn('name', ['finance', 'manage-supplier', 'stock', 'manage-grn'])
            ->pluck('id')->toArray();
        } elseif ($user->client_vendor_id) {
            $perm_ids = Permission::whereIn('name', ['crm'])
                ->pluck('id')->toArray();
        }
        PermissionUser::where('user_id', $user->id)->whereIn('permission_id', $perm_ids)->delete();
        foreach ($perm_ids as $key => $value) {
            $perm_ids[$key] = ['permission_id' => $value, 'user_id' => $user->id];
        }
        PermissionUser::insert($perm_ids);
        
        return true;
    }

    public function deleteAuth($entity, $user_type)
    {
        $query = Hrm::query()
            ->when($user_type == 'client_vendor', fn($q) => $q->where('client_vendor_id', $entity->id))
            ->when($user_type == 'client', fn($q) => $q->where('customer_id', $entity->id))
            ->when($user_type == 'supplier', fn($q) => $q->where('supplier_id', $entity->id));
        $user = $query->first();
        if ($user) {
            $this->removeAuthImage($user);
            PermissionUser::where('user_id', $user->id)->delete();
            $user->delete(); 
            return true;
        }
    }

    public function uploadAuthImage($image)
    {
        $path = 'img' . DIRECTORY_SEPARATOR . 'users' . DIRECTORY_SEPARATOR;
        $image_name = time() . $image->getClientOriginalName();
        $this->storage->put($path . $image_name, file_get_contents($image->getRealPath()));
        return $image_name;
    } 

    public function removeAuthImage($entity)
    {
        $path = 'img' . DIRECTORY_SEPARATOR . 'users' . DIRECTORY_SEPARATOR;
        $storage_exists = $this->storage->exists($path . $entity->picture);
        if ($entity->picture && $storage_exists) {
            $this->storage->delete($path . $entity->picture);
        }
        return true;
    }
}