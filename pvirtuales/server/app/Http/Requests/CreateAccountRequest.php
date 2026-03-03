<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Valida los datos del formulario de registro completo.
 * 
 * Se usa en el paso 4 (último) del flujo de registro:
 * El usuario rellena sus datos personales → se valida aquí → se crea la cuenta.
 */
class CreateAccountRequest extends FormRequest
{
    /**
     * Cualquier usuario (incluso no autenticado) puede hacer esta petición
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Reglas de validación
     */
    public function rules(): array
    {
        return [
            'token' => 'required|string',
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:150',
            'password' => 'required|string|min:8|confirmed',
            'birth_date' => 'required|date|before:today',
            'gender' => 'required|in:male,female,other',
        ];
    }

    /**
     * Mensajes de error personalizados en español
     */
    public function messages(): array
    {
        return [
            'token.required' => 'El token de invitación es obligatorio.',
            'first_name.required' => 'El nombre es obligatorio.',
            'first_name.max' => 'El nombre no puede exceder 100 caracteres.',
            'last_name.required' => 'Los apellidos son obligatorios.',
            'last_name.max' => 'Los apellidos no pueden exceder 150 caracteres.',
            'password.required' => 'La contraseña es obligatoria.',
            'password.min' => 'La contraseña debe tener al menos 8 caracteres.',
            'password.confirmed' => 'Las contraseñas no coinciden.',
            'birth_date.required' => 'La fecha de nacimiento es obligatoria.',
            'birth_date.date' => 'Introduce una fecha válida.',
            'birth_date.before' => 'La fecha de nacimiento debe ser anterior a hoy.',
            'gender.required' => 'El género es obligatorio.',
            'gender.in' => 'El género seleccionado no es válido.',
        ];
    }
}