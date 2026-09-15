<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'full_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'phone' => ['required', 'string', 'max:30'],
            'date_of_birth' => ['required', 'date', 'before:today'],
            'area' => ['required', 'in:North District,Central,South District'],
            'password' => ['required', 'string', 'min:8'],
        ];
    }
}
