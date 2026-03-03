<?php

namespace App\Services\AI;

use Illuminate\Support\Facades\Http;
use Exception;

class GrokService implements AIServiceInterface
{
    /**
     * Envía un mensaje a la API de xAI (Grok) y obtiene la respuesta.
     *
     * @param array $history Historial completo de la conversación
     * @param float $temperature Nivel de creatividad (0.0-1.0). Por defecto 0.7
     * @return string Respuesta generada por la IA
     * @throws Exception Si falta la API key o la petición falla
     */
    public function sendMessage(array $history, float $temperature = 0.7): string
    {
        // 1. Obtener la API key desde el archivo .env
        $apiKey = config('ai.providers.grok.api_key');

        if (!$apiKey) {
            throw new Exception("Falta la API Key de xAI");
        }

        // 2. Obtener el modelo desde el archivo de configuración config/ai.php
        $model = config('ai.providers.grok.default_model');

        // 3. Grok es compatible con la API de OpenAI, solo cambia la URL base
        $response = Http::withToken($apiKey)
            ->timeout(30) // Esperar máximo 30 segundos
            ->post('https://api.x.ai/v1/chat/completions', [
                'model' => $model,              // Modelo a usar (ej: grok-2-latest)
                'messages' => $history,         // Historial de mensajes
                'temperature' => $temperature,  // Creatividad proporcionada por el usuario
            ]);

        // 4. Verificar si la petición falló
        if ($response->failed()) {
            throw new Exception("Error Grok: " . $response->body());
        }

        // 5. Extraer y devolver el texto de la respuesta
        return $response->json('choices.0.message.content');
    }
}