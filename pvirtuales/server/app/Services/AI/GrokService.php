<?php

namespace App\Services\AI;

use Illuminate\Support\Facades\Http;
use Exception;

class GrokService implements AIServiceInterface
{
    public function sendMessage(array $history): string
    {
        $apiKey = env('XAI_API_KEY');

        if (!$apiKey) {
            throw new Exception("Falta la API Key de xAI");
        }

        // Grok es compatible con la API de OpenAI, solo cambia la URL y el modelo
        $response = Http::withToken($apiKey)
            ->timeout(30)
            ->post('https://api.x.ai/v1/chat/completions', [
                'model' => 'grok-2-latest', // O el modelo disponible
                'messages' => $history,
            ]);

        if ($response->failed()) {
            throw new Exception("Error Grok: " . $response->body());
        }

        return $response->json('choices.0.message.content');
    }
}