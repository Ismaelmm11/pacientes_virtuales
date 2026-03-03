<?php

namespace App\Services\AI;

use Illuminate\Support\Facades\Http;
use Exception;

class GeminiService implements AIServiceInterface
{
    /**
     * Envía un mensaje a la API de Google (Gemini) y obtiene la respuesta.
     *
     * @param array $history Historial completo de la conversación
     * @param float $temperature Nivel de creatividad (0.0-1.0). Por defecto 0.7
     * @return string Respuesta generada por la IA
     * @throws Exception Si falta la API key o la petición falla
     */
    public function sendMessage(array $history, float $temperature = 0.7): string
    {
        // 1. Obtener la API key desde el archivo .env
        $apiKey = config('ai.providers.gemini.api_key');

        if (!$apiKey) {
            throw new Exception("Falta la API Key de Google");
        }

        // 2. Obtener el modelo desde el archivo de configuración config/ai.php
        $model = config('ai.providers.gemini.default_model');

        // 3. Gemini usa un formato diferente: 'parts' y 'contents'
        $contents = [];
        $systemInstruction = null;

        foreach ($history as $msg) {
            if ($msg['role'] === 'system') {
                // Las instrucciones del sistema van en 'system_instruction'
                $systemInstruction = ['parts' => ['text' => $msg['content']]];
            } else {
                // Gemini llama 'model' al rol de asistente
                $role = ($msg['role'] === 'assistant') ? 'model' : 'user';
                $contents[] = [
                    'role' => $role,
                    'parts' => [['text' => $msg['content']]]
                ];
            }
        }

        // 4. Construir la URL con el modelo y la API key
        $url = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$apiKey}";

        // 5. Hacer la petición a la API de Gemini
        $response = Http::timeout(30)->post($url, [
            'system_instruction' => $systemInstruction,  // Instrucciones del sistema
            'contents' => $contents,                     // Mensajes de la conversación
            'generationConfig' => [
                'temperature' => $temperature,           // Creatividad proporcionada por el usuario
            ],
        ]);

        // 6. Verificar si la petición falló
        if ($response->failed()) {
            throw new Exception("Error Gemini: " . $response->body());
        }

        // 7. Extraer y devolver el texto de la respuesta
        return $response->json('candidates.0.content.parts.0.text');
    }
}