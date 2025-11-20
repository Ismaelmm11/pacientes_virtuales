<?php

namespace App\Services\AI;

use Illuminate\Support\Facades\Http;
use Exception;

class OpenAIService implements AIServiceInterface
{
    public function sendMessage(array $history): string
    {
        $apiKey = env('OPENAI_API_KEY');

        if (!$apiKey) {
            throw new Exception("Falta la API Key de OpenAI en el archivo .env");
        }

        $response = Http::withToken($apiKey)
            ->timeout(30) // Esperamos hasta 30 seg
            ->post('https://api.openai.com/v1/chat/completions', [
                'model' => 'gpt-4o-mini', // O 'gpt-4o' si tienes acceso/saldo
                'messages' => $history,
                'temperature' => 0.7, // Creatividad equilibrada
            ]);

        if ($response->failed()) {
            throw new Exception("Error OpenAI: " . $response->body());
        }

        return $response->json('choices.0.message.content');
    }
}