<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Valida el email del usuario antes de enviar el enlace de invitación.
 * 
 * Se usa en el paso 2 del flujo de registro:
 * El usuario introduce su email → se valida aquí → se envía el enlace.
 */
class SendInvitateLinkRequest extends FormRequest
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
            'email' => 'required|email|unique:users,email',
        ];
    }

    /**
     * Mensajes de error personalizados en español
     */
    public function messages(): array
    {
        return [
            'email.required' => 'El correo electrónico es obligatorio.',
            'email.email' => 'Introduce un correo electrónico válido.',
            'email.unique' => 'Este email ya está registrado. Por favor, inicia sesión.',
        ];
    }
}