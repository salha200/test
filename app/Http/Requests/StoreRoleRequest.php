<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreRoleRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'name' => 'required|unique:roles,name',
            'permission' => 'required|array',
            'permission.*' => 'exists:permissions,name',
        ];
    }
}
