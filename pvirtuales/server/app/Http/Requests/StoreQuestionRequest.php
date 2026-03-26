<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Validación para la creación/edición de preguntas del test.
 *
 * Las reglas cambian según el tipo de pregunta:
 * - MULTIPLE_CHOICE: Requiere opciones (array de 2-6) y respuesta correcta
 * - TRUE_FALSE: Requiere respuesta correcta (true/false)
 * - OPEN_ENDED: No requiere respuesta correcta ni feedback
 */
class StoreQuestionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        $type = $this->input('question_type');

        $rules = [
            'question_text' => 'required|string|max:1000',
            'question_type' => 'required|string|in:MULTIPLE_CHOICE,TRUE_FALSE,OPEN_ENDED',
            'is_required'   => 'boolean',
        ];

        // Reglas específicas según el tipo
        switch ($type) {
            case 'MULTIPLE_CHOICE':
                $rules['options']        = 'required|array|min:2|max:6';
                $rules['options.*']      = 'required|string|max:500';
                $rules['correct_answer'] = 'required|string|max:500';
                $rules['feedback_correct']   = 'nullable|string|max:1000';
                $rules['feedback_incorrect'] = 'nullable|string|max:1000';
                break;

            case 'TRUE_FALSE':
                $rules['correct_answer'] = 'required|string|in:true,false';
                $rules['feedback_correct']   = 'nullable|string|max:1000';
                $rules['feedback_incorrect'] = 'nullable|string|max:1000';
                break;

            case 'OPEN_ENDED':
                // Sin respuesta correcta ni feedback
                break;
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'question_text.required' => 'El enunciado de la pregunta es obligatorio.',
            'question_text.max'      => 'El enunciado no puede exceder 1000 caracteres.',
            'question_type.required' => 'Debes seleccionar un tipo de pregunta.',
            'question_type.in'       => 'El tipo de pregunta no es válido.',
            'points.required'        => 'La puntuación es obligatoria.',
            'options.required'       => 'Las opciones son obligatorias para preguntas de opción múltiple.',
            'options.min'            => 'Debes añadir al menos 2 opciones.',
            'options.max'            => 'No puedes añadir más de 6 opciones.',
            'options.*.required'     => 'Cada opción debe tener texto.',
            'correct_answer.required' => 'La respuesta correcta es obligatoria para este tipo de pregunta.',
            'correct_answer.in'       => 'La respuesta debe ser Verdadero o Falso.',
            'feedback_correct.max'    => 'El feedback no puede exceder 1000 caracteres.',
            'feedback_incorrect.max'  => 'El feedback no puede exceder 1000 caracteres.',
        ];
    }

    /**
     * Limpia los datos antes de validar.
     * Para OPEN_ENDED, elimina campos que no aplican.
     */
    protected function prepareForValidation(): void
    {
        $type = $this->input('question_type');

        if ($type === 'OPEN_ENDED') {
            $this->merge([
                'correct_answer'     => null,
                'feedback_correct'   => null,
                'feedback_incorrect' => null,
                'options'            => null,
            ]);
        }

        if ($type === 'TRUE_FALSE') {
            $this->merge([
                'options' => null,
            ]);
        }

        // Limpiar opciones vacías en múltiple elección
        if ($type === 'MULTIPLE_CHOICE' && $this->has('options')) {
            $options = array_filter($this->options, fn($opt) => !empty(trim($opt)));
            $this->merge(['options' => array_values($options)]);
        }
    }
}