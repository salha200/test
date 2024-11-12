<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreRoleRequest;
use App\Http\Requests\UpdateRoleRequest;
use App\Services\RoleService;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    protected $roleService;

    public function __construct(RoleService $roleService)
    {
        $this->roleService = $roleService;
    }

    // Get all roles
    public function index()
    {
        $roles = $this->roleService->getAllRoles();
        return response()->json(['status' => 'success', 'roles' => $roles]);
    }

    // Store a new role
    public function store(StoreRoleRequest $request)
    {
        $role = $this->roleService->createRole($request->validated());
        return response()->json(['status' => 'success', 'message' => 'Role created successfully', 'role' => $role]);
    }

    // Show a specific role with its permissions
    public function show($id)
    {
        $role = $this->roleService->getRoleById($id);
        if (!$role) {
            return response()->json(['status' => 'error', 'message' => 'Role not found'], 404);
        }
        return response()->json(['status' => 'success', 'role' => $role]);
    }

    // Update an existing role
    public function update(UpdateRoleRequest $request, $id)
    {
        $role = $this->roleService->updateRole($id, $request->validated());
        return response()->json(['status' => 'success', 'message' => 'Role updated successfully', 'role' => $role]);
    }

    // Delete a role
    public function destroy($id)
    {
        $deleted = $this->roleService->deleteRole($id);
        if (!$deleted) {
            return response()->json(['status' => 'error', 'message' => 'Role not found'], 404);
        }
        return response()->json(['status' => 'success', 'message' => 'Role deleted successfully']);
    }
}
