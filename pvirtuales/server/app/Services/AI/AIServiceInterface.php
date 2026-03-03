<?php

namespace App\Services\AI;

interface AIServiceInterface
{
    /**
     * Envía el historial de chat a la IA y obtiene la respuesta.
     *
     * @param array $history Array de mensajes estándar [['role' => 'user', 'content' => '...'], ...]
     * @param float $temperature Nivel de creatividad (0.0 = determinista, 1.0 = muy creativo)
     * @return string La respuesta de texto de la IA.
     */
    public function sendMessage(array $history, float $temperature = 0.7): string;
}
                                                                                          