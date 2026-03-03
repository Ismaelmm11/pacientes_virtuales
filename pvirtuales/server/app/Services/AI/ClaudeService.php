<?php

namespace App\Services\AI;

use Illuminate\Support\Facades\Http;
use Exception;

class ClaudeService implements AIServiceInterface
{
    /**
     * Envía un mensaje a la API de Anthropic (Claude) y obtiene la respuesta.
     *
     * @param array $history Historial completo de la conversación
     * @param float $temperature Nivel de creatividad (0.0-1.0). Por defecto 0.7
     * @return string Respuesta generada por la IA
     * @throws Exception Si falta la API key o la petición falla
     */
    public function sendMessage(array $history, float $temperature = 0.7): string
    {
        // 1. Obtener la API key desde el archivo .env
        $apiKey = config('ai.providers.claude.api_key');

        if (!$apiKey) {
            throw new Exception("Falta la API Key de Anthropic");
        }

        // 2. Obtener el modelo desde el archivo de configuración config/ai.php
        $model = config('ai.providers.claude.default_model');

        // 3. Claude requiere separar el 'system' prompt del resto de mensajes
        $systemPrompt = '';
        $messages = [];

        foreach ($history as $msg) {
            if ($msg['role'] === 'system') {
                // El system prompt va en un campo separado
                $systemPrompt = $msg['content'];
            } else {
                // Los mensajes user/assistant van en el array 'messages'
                $messages[] = $msg;
            }
        }

        // 4. Hacer la petición a la API de Claude
        $response = Http::withHeaders([
            'x-api-key' => $apiKey,
            'anthropic-version' => '2023-06-01',
            'content-type' => 'application/json',
        ])->timeout(30)->post('https://api.anthropic.com/v1/messages', [
            'model' => $model,              // Modelo a usar (ej: claude-haiku-4-5)
            'system' => $systemPrompt,      // Instrucciones del sistema
            'messages' => $messages,        // Mensajes de la conversación
            'max_tokens' => 1024,           // Límite de tokens en la respuesta
            'temperature' => $temperature,  // Creatividad proporcionada por el usuario
        ]);

        // 5. Verificar si la petición falló
        if ($response->failed()) {
            throw new Exception("Error Claude: " . $response->body());
        }

        // 6. Extraer y devolver el texto de la respuesta
        return $response->json('content.0.text');
    }
}