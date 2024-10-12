<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'username' => 'required|unique:pos_users,username|regex:/^[a-zA-Z0-9._-]+$/',
            'email' => 'required|email|unique:pos_users,email',
            'password' => 'required|min:6',
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'gender' => 'nullable|string|max:10',
            'group_id' => 'nullable|exists:pos_groups,id',
            'biller_id' => 'nullable|exists:pos_companies,id',
            'warehouse_id' => 'required|exists:pos_warehouses,id',
            'status' => 'nullable|boolean',
        ];
    }
}
