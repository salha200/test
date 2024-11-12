<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Hash;

class UserService
{
    public function getAllUsers()
    {
        return User::with('roles')->get();
    }

    public function getUserById($id)
    {
        return User::with('roles')->find($id);
    }

    public function updateUserService($id, $data)
    {
        $user = User::find($id);
        if (!$user) return null;

        if (isset($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            $data = Arr::except($data, ['password']);
        }

        $user->update($data);
        if (isset($data['role_name'])) {
            $user->syncRoles([$data['role_name']]);
        }
        return $user;
    }

    public function deleteUser($id)
    {
        $user = User::find($id);
        if ($user) {
            $user->delete();
            return true;
        }
        return false;
    }
}
