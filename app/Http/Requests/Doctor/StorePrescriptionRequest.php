<?php

namespace App\Http\Requests\Doctor;

use Illuminate\Foundation\Http\FormRequest;

class StorePrescriptionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'medication' => ['required', 'string', 'max:255'],
            'dosage' => ['required', 'string', 'max:255'],
            'instructions' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
