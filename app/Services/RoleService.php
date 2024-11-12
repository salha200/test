<?php

namespace App\Services;

use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleService
{
    // Get all roles
    public function getAllRoles()
    {
        return Role::with('permissions')->get();
    }

    // Get role by ID with permissions
    public function getRoleById($id)
    {
        $role = Role::with('permissions')->find($id);
        return $role;
    }

    // Create a new role
    public function createRole($data)
    {
        $role = Role::create(['name' => $data['name']]);
        $role->syncPermissions($data['permission']);
        return $role;
    }

    // Update an existing role
    public function updateRole($id, $data)
    {
        $role = Role::find($id);
        if (!$role) return null;

        $role->name = $data['name'];
        $role->save();
        $role->syncPermissions($data['permission']);
        return $role;
    }

    // Delete a role
    public function deleteRole($id)
    {
        $role = Role::find($id);
        if ($role) {
            $role->delete();
            return true;
        }
        return false;
    }
}
