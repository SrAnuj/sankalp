<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateSankalpRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'user_id' => [
                'sometimes',
                'required',
                'integer',
                Rule::exists('users', 'id'),
            ],

            'naam_library_id' => [
                'sometimes',
                'required',
                'integer',
                Rule::exists('naam_library', 'id')
                    ->where('status', 'active'),
            ],

            'target_count' => [
                'sometimes',
                'required',
                'integer',
                Rule::in([1008, 5000, 11000, 21000, 51000, 100000]),
            ],
        ];
    }
}