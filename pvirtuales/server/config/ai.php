<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Configuración de Proveedores de IA
    |--------------------------------------------------------------------------
    |
    | Aquí se definen los modelos disponibles para cada proveedor de IA.
    | 
    | - 'models': Lista de modelos que se pueden usar
    | - 'default_model': El modelo que se usará si no se especifica otro
    |
    | La temperatura NO se define aquí, la proporciona el usuario en cada
    | simulación (puede ser fija, aleatoria, o personalizada).
    |
    */

    'providers' => [

        // OpenAI (ChatGPT)
        'openai' => [
            'api_key' => env('OPENAI_API_KEY'),
            'models' => [
                'gpt-4o',           // Modelo más potente y reciente
                'gpt-4o-mini',      // Versión ligera y económica
                'gpt-4-turbo',      // Versión optimizada para velocidad
            ],
            'default_model' => 'gpt-4o-mini',  // Usamos el más económico por defecto
        ],

        // Anthropic (Claude)
        'claude' => [
            'api_key' => env('ANTHROPIC_API_KEY'),
            'models' => [
                'claude-opus-4-5-20251101',    // Modelo más potente
                'claude-sonnet-4-5-20250929',  // Equilibrio precio/calidad
                'claude-haiku-4-5-20251001',   // Más rápido y económico
            ],
            'default_model' => 'claude-haiku-4-5-20251001',  // Usamos Haiku por defecto
        ],

        // Google (Gemini)
        'gemini' => [
            'api_key' => env('GOOGLE_API_KEY'),
            'models' => [
                'gemini-3-flash',  // Rápido y eficiente
                'gemini-2.5-pro',    // Más potente para tareas complejas
            ],
            'default_model' => 'gemini-3-flash',  // Flash es suficiente para simulaciones
        ],

        // xAI (Grok)
        'grok' => [
            'api_key' => env('XAI_API_KEY'),
            'models' => [
                'grok-2-latest',  // Última versión estable
                'grok-beta',      // Versión experimental (puede ser inestable)
            ],
            'default_model' => 'grok-2-latest',  // Usamos la versión estable
        ],

        // Mistral AI
        'mistral' => [
            'api_key' => env('MISTRAL_API_KEY'),
            'models' => [
                'mistral-large-latest',  // Modelo grande para tareas complejas
                'mistral-small-latest',  // Modelo pequeño y rápido
            ],
            'default_model' => 'mistral-small-latest',  // Small es suficiente para la mayoría de casos
        ],
    ],
];