<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class ReassignPatientRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'doctor_id' => ['required', 'integer', 'exists:doctors,id'],
            'reason' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
