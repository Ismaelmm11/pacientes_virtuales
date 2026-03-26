<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTestConfigRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'max_attempts'        => 'required|integer|min:-1|max:10',
            'randomize_questions' => 'required|boolean',
            'questions_per_test' => ['required_if:randomize_questions,1', 'nullable', 'integer', 'min:1'],
            'randomize_order'     => 'required|boolean',
        ];
    }

    public function messages(): array
{
    return [
        'questions_per_test.required_if' => 'Debes especificar cuántas preguntas aparecerán por test cuando la aleatorización está activa.',
    ];
}
}
