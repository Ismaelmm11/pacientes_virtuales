<?php

namespace App\Http\Controllers\Simulation;

use App\Http\Controllers\Controller;
use App\Http\Requests\SendMessageRequest;
use App\Models\Patient;
use App\Services\AI\AIFactory;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;

/**
 * Gestiona las simulaciones de consulta médica en tiempo real.
 * 
 * Controla dos momentos clave:
 * 1. Inicio de la simulación → Carga el paciente y prepara la sesión
 * 2. Envío de mensajes → Comunica con la IA y devuelve la respuesta
 * 
 * El estado de la conversación se almacena en la sesión del usuario
 * (historial de chat, modelo de IA seleccionado, paciente actual).
 */
class SimulationController extends Controller
{
    /**
     * Inicia una nueva simulación de consulta médica.
     * 
     * Carga el paciente, configura el prompt del sistema y la frase inicial,
     * y guarda todo en sesión para mantener el contexto durante el chat.
     *
     * @param string $aiModel Clave del proveedor de IA (openai, claude, gemini, etc.)
     * @param int $patientId ID del paciente virtual
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function start(string $aiModel, int $patientId)
    {
        $patient = Patient::with(['knowledgeBase'])->findOrFail($patientId);

        // Verificar que el paciente tiene los datos mínimos para funcionar
        if (!$patient->prompt || !$patient->prompt->prompt_content) {
            return back()->with('error', 'Este paciente no tiene un prompt generado.');
        }

        if (!$patient->knowledgeBase || !$patient->knowledgeBase->frase_inicial) {
            return back()->with('error', 'Este paciente no tiene una frase inicial configurada.');
        }

        // Limpiar cualquier simulación previa
        Session::forget('chat_history');

        // Construir el historial inicial con el prompt del sistema y la frase de bienvenida
        $chatHistory = [
            ['role' => 'system', 'content' => $patient->prompt->prompt_content],
            ['role' => 'assistant', 'content' => $patient->knowledgeBase->frase_inicial],
        ];

        // Guardar el estado de la simulación en sesión
        Session::put('chat_history', $chatHistory);
        Session::put('current_ai', $aiModel);
        Session::put('current_patient_id', $patient->id);

        return view('pages.simulation.chat', [
            'aiModel' => $aiModel,
            'patient' => [
                'id' => $patient->id,
                'name' => $this->extractPatientName($patient),
                'case_title' => $patient->case_title,
            ],
            'history' => $chatHistory,
        ]);
    }

    /**
     * Procesa un mensaje del usuario y obtiene la respuesta de la IA.
     * 
     * Flujo:
     * 1. Añade el mensaje del usuario al historial
     * 2. Envía el historial completo a la IA seleccionada
     * 3. Añade la respuesta de la IA al historial
     * 4. Devuelve la respuesta en formato JSON
     *
     * @param SendMessageRequest $request Petición validada con el campo 'message'
     * @return \Illuminate\Http\JsonResponse
     */
    public function sendMessage(SendMessageRequest $request)
    {
        $history = Session::get('chat_history', []);
        $aiModel = Session::get('current_ai');

        // Verificar que la sesión sigue activa
        if (!$aiModel || empty($history)) {
            return response()->json([
                'error' => 'Sesión caducada. Recarga la página.',
            ], 419);
        }

        // Añadir el mensaje del usuario al historial
        $history[] = ['role' => 'user', 'content' => $request->input('message')];

        try {
            // Enviar el historial completo a la IA y obtener la respuesta
            $aiService = AIFactory::create($aiModel);
            $aiResponse = $aiService->sendMessage($history);

            // Añadir la respuesta al historial y guardar en sesión
            $history[] = ['role' => 'assistant', 'content' => $aiResponse];
            Session::put('chat_history', $history);

            return response()->json(['response' => $aiResponse]);

        } catch (\Exception $e) {
            Log::error('Error en simulación', [
                'ai' => $aiModel,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'error' => 'Error del Sistema: ' . $e->getMessage(),
            ], 500);
        }
    }

    // ==================== MÉTODOS AUXILIARES ====================

    /**
     * Extrae el nombre del paciente desde el título del caso.
     * Asume formato "Nombre - Descripción" y toma la primera parte.
     *
     * @param Patient $patient
     * @return string
     */
    private function extractPatientName(Patient $patient): string
    {
        $parts = explode(' - ', $patient->case_title);

        return $parts[0] ?? 'Paciente';
    }
}