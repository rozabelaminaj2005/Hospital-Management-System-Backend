<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreDoctorRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'specialty' => ['required', 'string', 'max:255'],
            'department_id' => ['required', 'integer', 'exists:departments,id'],
            'area' => ['required', 'in:North District,Central,South District'],
            'status' => ['nullable', 'in:active,inactive'],
            'phone' => ['nullable', 'string', 'max:30'],
        ];
    }
}
