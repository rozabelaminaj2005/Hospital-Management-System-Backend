<?php

namespace App\Http\Requests\Patient;

use Illuminate\Foundation\Http\FormRequest;

class StoreSymptomReportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'symptom_ids' => ['required', 'array', 'min:1'],
            'symptom_ids.*' => ['integer', 'exists:symptoms,id'],
            'description' => ['required', 'string', 'max:2000'],
            'duration' => ['required', 'in:hours,day,days,week'],
            'pain_level' => ['nullable', 'integer', 'min:1', 'max:10'],
            'use_ai' => ['required', 'boolean'],
        ];
    }
}
