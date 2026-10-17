<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BulkDeleteSankalpRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'ids' => [
                'required',
                'array',
                'min:1',
            ],

            'ids.*' => [
                'required',
                'integer',
                'distinct',
                Rule::exists('sankalps', 'id'),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'ids.required' => 'Please select at least one Sankalp.',
            'ids.array' => 'IDs must be provided as an array.',
            'ids.min' => 'Please select at least one Sankalp.',
            'ids.*.distinct' => 'Duplicate Sankalp IDs are not allowed.',
            'ids.*.exists' => 'One or more selected Sankalps do not exist.',
        ];
    }
}