<?php

namespace App\Http\Requests\User;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->role <= User::ROLE_ADMIN;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')],
            'password' => ['nullable', 'string', 'min:8'],
            'phone' => ['nullable', 'string', 'max:20'],
            'department' => ['nullable', 'string', 'max:100'],
            'role' => ['required', 'integer', Rule::in([
                User::ROLE_SUPER_ADMIN,
                User::ROLE_ADMIN,
                User::ROLE_STAFF,
                User::ROLE_USER,
            ])],
            'company_id' => [
                $this->user()?->role === User::ROLE_SUPER_ADMIN ? 'required' : 'nullable',
                'integer',
                Rule::exists('companies', 'id'),
            ],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }
}
