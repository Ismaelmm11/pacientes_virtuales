<?php

namespace App\Services\AI;

use Exception;

class AIFactory
{
    public static function create(string $modelKey): AIServiceInterface
    {
        return match ($modelKey) {
            'gpt' => new OpenAIService(),
            'claude' => new ClaudeService(),
            'gemini' => new GeminiService(),
            'grok' => new GrokService(),
            'mistral' => new MistralService(),
            default => throw new Exception("Modelo de IA no soportado: $modelKey"),
        };
    }
}