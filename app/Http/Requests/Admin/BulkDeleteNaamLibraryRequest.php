<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class BulkDeleteNaamLibraryRequest extends FormRequest
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
                'exists:naam_library,id',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'ids.required' => 'Please select at least one Naam to delete.',
            'ids.array' => 'Invalid IDs format.',
            'ids.min' => 'Please select at least one Naam to delete.',
            'ids.*.required' => 'Each Naam ID is required.',
            'ids.*.integer' => 'Naam IDs must be integers.',
            'ids.*.distinct' => 'Duplicate Naam IDs are not allowed.',
            'ids.*.exists' => 'One or more selected Naams do not exist.',
        ];
    }
}