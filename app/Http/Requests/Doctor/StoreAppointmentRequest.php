<?php

namespace App\Http\Requests\Doctor;

use Illuminate\Foundation\Http\FormRequest;

class StoreAppointmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'department_id' => ['required', 'integer', 'exists:departments,id'],
            'scheduled_at' => ['required', 'date', 'after:now'],
            'duration_minutes' => ['nullable', 'integer', 'min:5', 'max:240'],
            'reason' => ['nullable', 'string', 'max:500'],
            'location' => ['nullable', 'string', 'max:255'],
        ];
    }
}
