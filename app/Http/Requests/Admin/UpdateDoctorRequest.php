<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateDoctorRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $doctor = $this->route('doctor');

        return [
            'first_name' => ['sometimes', 'string', 'max:255'],
            'last_name' => ['sometimes', 'string', 'max:255'],
            'email' => ['sometimes', 'email', 'max:255', Rule::unique('users', 'email')->ignore($doctor->user_id)],
            'specialty' => ['sometimes', 'string', 'max:255'],
            'department_id' => ['sometimes', 'integer', 'exists:departments,id'],
            'area' => ['sometimes', 'in:North District,Central,South District'],
            'phone' => ['nullable', 'string', 'max:30'],
        ];
    }
}
