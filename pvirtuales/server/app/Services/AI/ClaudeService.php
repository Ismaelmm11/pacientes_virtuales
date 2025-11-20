<?php

namespace App\Services\AI;

use Illuminate\Support\Facades\Http;
use Exception;

class ClaudeService implements AIServiceInterface
{
    public function sendMessage(array $history): string
    {
        $apiKey = env('ANTHROPIC_API_KEY');

        if (!$apiKey) {
            throw new Exception("Falta la API Key de Anthropic");
        }

        // Claude requiere separar el 'system' prompt del resto
        $systemPrompt = '';
        $messages = [];

        foreach ($history as $msg) {
            if ($msg['role'] === 'system') {
                $systemPrompt = $msg['content'];
            } else {
                $messages[] = $msg;
            }
        }

        $response = Http::withHeaders([
            'x-api-key' => $apiKey,
            'anthropic-version' => '2023-06-01',
            'content-type' => 'application/json',
        ])->timeout(30)->post('https://api.anthropic.com/v1/messages', [
            'model' => 'claude-haiku-4-5-20251001',
            'system' => $systemPrompt,
            'messages' => $messages,
            'max_tokens' => 1024,
        ]);

        if ($response->failed()) {
            throw new Exception("Error Claude: " . $response->body());
        }

        return $response->json('content.0.text');
    }
}