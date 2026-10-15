<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateNaamLibraryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $naamId = $this->route('naam');

        return [
            'name' => ['required', 'string', 'max:150'],
            'name_hindi' => ['nullable', 'string', 'max:150'],
            'name_punjabi' => ['nullable', 'string', 'max:150'],

            'slug' => [
                'sometimes', 'required', 'string', 'max:180',
                'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
                Rule::unique('naam_library', 'slug')->ignore($naamId),
            ],

            'description' => ['nullable', 'string'],
            'category' => ['nullable', 'string', 'max:100'],
            'icon' => ['nullable', 'string', 'max:255'],
            'status' => ['sometimes', Rule::in(['active', 'inactive'])],
            'sort_order' => ['sometimes', 'integer', 'min:0'],
        ];
    }
}
