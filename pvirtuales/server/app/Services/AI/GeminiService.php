<?php

namespace App\Services\AI;

use Illuminate\Support\Facades\Http;
use Exception;

class GeminiService implements AIServiceInterface
{
    public function sendMessage(array $history): string
    {
        $apiKey = env('GOOGLE_API_KEY');

        if (!$apiKey) {
            throw new Exception("Falta la API Key de Google");
        }

        // Conversión de formato: Gemini usa 'parts' y 'contents'
        $contents = [];
        $systemInstruction = null;

        foreach ($history as $msg) {
            if ($msg['role'] === 'system') {
                $systemInstruction = ['parts' => ['text' => $msg['content']]];
            } else {
                // Gemini llama 'model' al asistente
                $role = ($msg['role'] === 'assistant') ? 'model' : 'user';
                $contents[] = [
                    'role' => $role,
                    'parts' => [['text' => $msg['content']]]
                ];
            }
        }

        $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent?key={$apiKey}";

        $response = Http::timeout(30)->post($url, [
            'system_instruction' => $systemInstruction,
            'contents' => $contents,
        ]);

        if ($response->failed()) {
            throw new Exception("Error Gemini: " . $response->body());
        }

        return $response->json('candidates.0.content.parts.0.text');
    }
}