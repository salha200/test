<?php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UserRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $this->id,
            'password' => $this->isMethod('post') ? 'required|confirmed' : 'nullable|confirmed',
            'status' => 'required',
            'role_name' => 'required|string'
        ];
    }
}
