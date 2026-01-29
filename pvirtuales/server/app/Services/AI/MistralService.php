<?php

namespace App\Services\AI;

use Illuminate\Support\Facades\Http;
use Exception;

class MistralService implements AIServiceInterface
{
    public function sendMessage(array $history): string
    {
        $apiKey = env('MISTRAL_API_KEY');

        if (!$apiKey) {
            throw new Exception("Falta la API Key de Mistral");
        }

        // Mistral usa el mismo formato que OpenAI (messages con system, user, assistant)
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $apiKey,
            'Content-Type' => 'application/json',
        ])->timeout(30)->post('https://api.mistral.ai/v1/chat/completions', [
            'model' => 'mistral-small-latest',
            'messages' => $history,
            'max_tokens' => 1024,
        ]);

        if ($response->failed()) {
            throw new Exception("Error Mistral: " . $response->body());
        }

        return $response->json('choices.0.message.content');
    }
}