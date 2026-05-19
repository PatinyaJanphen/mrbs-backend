<?php

namespace App\Http\Requests\Profile;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePasswordRequest extends FormRequest
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
        $user = $this->user();
        $hasPassword = $user && !empty($user->password);

        $rules = [
            'password' => 'required|string|min:8|confirmed',
        ];

        if ($hasPassword) {
            $rules['current_password'] = 'required|string';
        }

        return $rules;
    }
}
