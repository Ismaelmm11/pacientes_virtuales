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
        $user = auth()->user();

        // ── Control de acceso para alumnos ──────────────────────────────────
        // Los profesores y admins pueden acceder siempre (para probar pacientes)
        if (!$user->isTeacher() && !$user->isAdmin()) {

            // El paciente debe estar publicado
            if (!$patient->is_published) {
                abort(403, 'Este paciente no está disponible.');
            }

            // El alumno debe estar matriculado en la asignatura del paciente
            $enrolledSubjectIds = $user->enrolledSubjects()->pluck('subjects.id');
            if (!$enrolledSubjectIds->contains($patient->subject_id)) {
                abort(403, 'No estás matriculado en la asignatura de este paciente.');
            }

            // Comprobar intentos restantes solo si no hay ya uno pendiente
            // (si hay uno pendiente lo reutilizamos y no consume un intento nuevo)
            $pendingAttempt = \App\Models\TestAttempt::where('user_id', $user->id)
                ->where('patient_id', $patient->id)
                ->whereNull('final_score')
                ->first();

            if (!$pendingAttempt && $patient->max_attempts !== -1) {
                $attemptsUsed = \App\Models\TestAttempt::where('user_id', $user->id)
                    ->where('patient_id', $patient->id)
                    ->count();
                if ($attemptsUsed >= $patient->max_attempts) {
                    return back()->with('error', 'Has agotado todos tus intentos para este paciente.');
                }
            }
        }

        // Verificar que el paciente tiene los datos mínimos para funcionar
        if (!$patient->prompt || !$patient->prompt->prompt_content) {
            return back()->with('error', 'Este paciente no tiene un prompt generado.');
        }

        if (!$patient->knowledgeBase || !$patient->knowledgeBase->frase_inicial) {
            return back()->with('error', 'Este paciente no tiene una frase inicial configurada.');
        }

        // Limpiar cualquier simulación previa en sesión
        Session::forget('chat_history');

        // Construir el historial inicial: prompt de sistema + frase de bienvenida
        $chatHistory = [
            ['role' => 'system', 'content' => $patient->prompt->prompt_content],
            ['role' => 'assistant', 'content' => $patient->knowledgeBase->frase_inicial],
        ];

        // Guardar estado de la simulación en sesión
        Session::put('chat_history', $chatHistory);
        Session::put('current_ai', $aiModel);
        Session::put('current_patient_id', $patient->id);

        // ── Crear TestAttempt solo para alumnos ─────────────────────────────
        // Los profesores no generan registros; usan la simulación para probar
        if (!$user->isTeacher() && !$user->isAdmin()) {
            $attempt = \App\Models\TestAttempt::create([
                'user_id' => $user->id,
                'patient_id' => $patient->id,
            ]);
            Session::put('current_attempt_id', $attempt->id);
        } else {
            Session::forget('current_attempt_id');
        }

        return view('pages.simulation.chat', [
            'aiModel' => $aiModel,
            'patient' => [
                'id' => $patient->id,
                'name' => $this->extractPatientName($patient)[0],
                'case_title' => $patient->case_title,
                'patient_description' => $patient->patient_description,
                'isTeacher' => $user->isTeacher() || $user->isAdmin(),
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

        $userMessage = $request->input('message');
        $isFarewell = $request->boolean('is_farewell', false);

        // Guardar en sesión el mensaje limpio (sin instrucciones internas)
        $history[] = ['role' => 'user', 'content' => $userMessage];

        // Si es despedida, construir un historial temporal con wrapper de contexto solo para esta llamada
        if ($isFarewell) {
            $farewellNote = implode("\n", [
                "[NOTA DE SISTEMA — NO LEER EN VOZ ALTA: El estudiante está cerrando la consulta con el siguiente mensaje.",
                "Responde como el paciente se despide de forma natural, acorde a tu personalidad.",
                "No inicies nuevos temas clínicos. Puedes incluir gestos finales entre corchetes si aportan expresividad al cierre.]",
                "",
                $userMessage,
            ]);
            $historyForAI = array_slice($history, 0, -1);
            $historyForAI[] = ['role' => 'user', 'content' => $farewellNote];
        } else {
            $historyForAI = $history;
        }

        try {
            $aiService = AIFactory::create($aiModel);
            $aiResponse = $aiService->sendMessage($historyForAI);

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
    private function extractPatientName(Patient $patient): array
    {
        $parts = explode(' - ', $patient->case_title);

        return $parts;
    }
}