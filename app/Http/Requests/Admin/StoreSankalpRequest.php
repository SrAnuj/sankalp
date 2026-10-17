<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreSankalpRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'user_id' => [
                'required',
                'integer',
                Rule::exists('users', 'id'),
            ],

            'naam_library_id' => [
                'required',
                'integer',
                Rule::exists('naam_library', 'id')
                    ->where('status', 'active'),
            ],

            'target_count' => [
                'required',
                'integer',
                Rule::in([1008, 5000, 11000, 21000, 51000, 100000]),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'user_id.required' => 'Please select a user.',
            'user_id.exists' => 'The selected user does not exist.',

            'naam_library_id.required' => 'Please select a Naam.',
            'naam_library_id.exists' => 'The selected Naam is invalid or inactive.',

            'target_count.required' => 'Please select a Sankalp target.',
            'target_count.in' => 'The selected target count is invalid.',
        ];
    }
}