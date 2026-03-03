<?php

namespace App\Services\AI;

use Exception;

class AIFactory
{
    /**
     * Crea una instancia del servicio de IA según el proveedor.
     * 
     * @param string $providerKey Clave del proveedor (openai, claude, gemini, grok, mistral)
     * @return AIServiceInterface
     * @throws Exception
     */
    public static function create(string $providerKey): AIServiceInterface
    {
        return match ($providerKey) {
            'openai' => new OpenAIService(),   // ✅ Ahora coincide con config/ai.php
            'claude' => new ClaudeService(),
            'gemini' => new GeminiService(),
            'grok' => new GrokService(),
            'mistral' => new MistralService(),
            default => throw new Exception("Proveedor de IA no soportado: $providerKey"),
        };
    }
}