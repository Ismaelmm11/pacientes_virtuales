<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Valida el mensaje que el usuario envía durante una simulación de consulta.
 * 
 * Se asegura de que el mensaje no esté vacío y no exceda el límite
 * de caracteres permitido para evitar abusos o errores.
 */
class SendMessageRequest extends FormRequest
{
    /**
     * Solo usuarios autenticados pueden enviar mensajes
     */
    public function authorize(): bool
    {
        return auth()->check();
    }

    /**
     * Reglas de validación
     */
    public function rules(): array
    {
        return [
            'message' => 'required|string|max:1000',
        ];
    }

    /**
     * Mensajes de error personalizados en español
     */
    public function messages(): array
    {
        return [
            'message.required' => 'El mensaje no puede estar vacío.',
            'message.max' => 'El mensaje no puede exceder 1000 caracteres.',
        ];
    }
}