<?php

namespace App\Services\AI;

use Illuminate\Support\Facades\Http;
use Exception;

class MistralService implements AIServiceInterface
{
    /**
     * Envía un mensaje a la API de Mistral AI y obtiene la respuesta.
     *
     * @param array $history Historial completo de la conversación
     * @param float $temperature Nivel de creatividad (0.0-2.0). Por defecto 0.7
     * @return string Respuesta generada por la IA
     * @throws Exception Si falta la API key o la petición falla
     */
    public function sendMessage(array $history, float $temperature = 0.7): string
    {
        // 1. Obtener la API key desde el archivo .env
        $apiKey = config('ai.providers.mistral.api_key');

        if (!$apiKey) {
            throw new Exception("Falta la API Key de Mistral");
        }

        // 2. Obtener el modelo desde el archivo de configuración config/ai.php
        $model = config('ai.providers.mistral.default_model');

        // 3. Mistral usa el mismo formato que OpenAI (messages con system, user, assistant)
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $apiKey,
            'Content-Type' => 'application/json',
        ])->timeout(30)->post('https://api.mistral.ai/v1/chat/completions', [
            'model' => $model,              // Modelo a usar (ej: mistral-small-latest)
            'messages' => $history,         // Historial de mensajes
            'max_tokens' => 1024,           // Límite de tokens en la respuesta
            'temperature' => $temperature,  // Creatividad proporcionada por el usuario
        ]);

        // 4. Verificar si la petición falló
        if ($response->failed()) {
            throw new Exception("Error Mistral: " . $response->body());
        }

        // 5. Extraer y devolver el texto de la respuesta
        return $response->json('choices.0.message.content');
    }
}