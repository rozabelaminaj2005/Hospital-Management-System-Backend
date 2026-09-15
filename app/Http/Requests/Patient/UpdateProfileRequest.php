<?php

namespace App\Http\Requests\Patient;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'phone' => ['sometimes', 'string', 'max:30'],
            'email' => ['sometimes', 'email', 'max:255', Rule::unique('users', 'email')->ignore($this->user()->id)],
            'area' => ['sometimes', 'in:North District,Central,South District'],
        ];
    }
}
