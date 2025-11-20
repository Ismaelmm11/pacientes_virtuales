<?php

namespace App\Services\AI;

interface AIServiceInterface
{
    /**
     * Envía el historial de chat a la IA y obtiene la respuesta.
     *
     * @param array $history Array de mensajes estándar [['role' => 'user', 'content' => '...'], ...]
     * @return string La respuesta de texto de la IA.
     */
    public function sendMessage(array $history): string;
}