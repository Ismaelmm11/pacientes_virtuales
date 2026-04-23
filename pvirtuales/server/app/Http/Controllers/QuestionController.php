<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateTestConfigRequest;
use App\Models\Patient;
use App\Models\Question;
use App\Http\Requests\StoreQuestionRequest;
use Illuminate\Support\Facades\Auth;
use App\Models\Answer;
use App\Models\TestAttempt;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;


/**
 * Gestiona el CRUD de preguntas del test de evaluación de un paciente.
 *
 * FLUJO:
 *   Preview del paciente → "Crear Test" → manage() → Formulario de preguntas
 *   El profesor añade/edita/elimina preguntas.
 *   Al publicar, se verifica que haya al menos 1 pregunta.
 *
 * SEGURIDAD:
 *   Solo el creador del paciente puede gestionar sus preguntas.
 */
class QuestionController extends Controller
{

    private function recalculatePoints(Patient $patient): void
    {
        $total = $patient->questions()->count();
        if ($total === 0)
            return;

        // Si hay aleatorización con límite definido, usar ese número como divisor
        $divisor = ($patient->randomize_questions && $patient->questions_per_test)
            ? $patient->questions_per_test
            : $total;

        $points = round(10 / $divisor, 2);

        $patient->questions()->update(['points' => $points]);
    }


    /**
     * Muestra la página de gestión del test con las preguntas existentes
     * y el formulario para añadir nuevas.
     */
    public function manage(Patient $patient)
    {
        $this->authorizeOwnership($patient);

        $questions = $patient->questions()->orderBy('created_at')->get();

        return view('pages.patients.test', compact('patient', 'questions'));
    }

    /**
     * Almacena una nueva pregunta para el test del paciente.
     */
    public function store(StoreQuestionRequest $request, Patient $patient)
    {
        $this->authorizeOwnership($patient);

        $data = $request->validated();
        $data['patient_id'] = $patient->id;

        Question::create($data);

        $this->recalculatePoints($patient);

        return redirect()
            ->route('teacher.patients.test', $patient)
            ->with('success', 'Pregunta añadida correctamente.');
    }

    /**
     * Elimina una pregunta del test.
     */
    public function destroy(Patient $patient, Question $question)
    {
        $this->authorizeOwnership($patient);

        // Verificar que la pregunta pertenece a este paciente y no a otro
        if ($question->patient_id !== $patient->id) {
            abort(403);
        }

        $question->delete();

        $this->recalculatePoints($patient);

        return redirect()
            ->route('teacher.patients.test', $patient)
            ->with('success', 'Pregunta eliminada.');
    }

    public function update(StoreQuestionRequest $request, Patient $patient, Question $question)
    {
        $this->authorizeOwnership($patient);

        if ($question->patient_id !== $patient->id)
            abort(403);

        $question->update($request->validated());
        $this->recalculatePoints($patient);

        return redirect()
            ->route('teacher.patients.test', $patient)
            ->with('success', 'Pregunta actualizada correctamente.');
    }

    public function updateConfig(UpdateTestConfigRequest $request, Patient $patient)
    {
        $this->authorizeOwnership($patient);

        if ($patient->is_published && $request->boolean('randomize_questions')) {
            $error = $patient->validateRandomConfig((int) $request->input('questions_per_test'));
            if ($error) {
                return redirect()
                    ->route('teacher.patients.test', $patient)
                    ->with('error', $error);
            }
        }

        if (!$request->boolean('randomize_questions')) {
            $patient->questions()->update(['is_required' => false]);
        }

        $patient->update($request->validated());

        return redirect()
            ->route('teacher.patients.test', $patient)
            ->with('success', 'Configuración guardada correctamente.');
    }

    /**
     * Verifica que el usuario autenticado es el creador del paciente.
     * Si no, lanza un 403 Forbidden.
     */
    private function authorizeOwnership(Patient $patient): void
    {
        if ($patient->created_by_user_id !== Auth::id() && !Auth::user()->isAdmin()) {
            abort(403, 'No tienes permiso para gestionar este paciente.');
        }
    }

    /**
     * Muestra el test al alumno para que responda las preguntas
     * tras haber completado la simulación.
     */
    /**
     * Muestra el test al alumno tras completar la simulación.
     *
     * Aplica la lógica de aleatorización configurada por el profesor:
     *   - randomize_questions: selecciona un subconjunto aleatorio del banco
     *   - randomize_order: baraja el orden de las preguntas seleccionadas
     *
     * También guarda la transcripción del chat en el TestAttempt activo.
     */
    public function take(Patient $patient)
    {
        $userId = auth()->id();
        $attemptId = Session::get('current_attempt_id');

        // Buscar el intento en sesión primero
        $attempt = null;
        if ($attemptId) {
            $attempt = TestAttempt::where('id', $attemptId)
                ->where('user_id', $userId)
                ->where('patient_id', $patient->id)
                ->first();
        }

        // Fallback a BD: el intento más reciente no enviado aún para este alumno y paciente
        if (!$attempt) {
            $attempt = TestAttempt::where('user_id', $userId)
                ->where('patient_id', $patient->id)
                ->whereNull('submitted_at')
                ->latest()
                ->first();
        }

        if (!$attempt) {
            return redirect()->route('student.patients.index')
                ->with('error', 'Debes completar una simulación antes de acceder al cuestionario.');
        }


        // Refrescar sesión por si se había perdido
        Session::put('current_attempt_id', $attempt->id);

        // ── Selección de preguntas ───────────────────────────────────────────
        if ($patient->randomize_questions && $patient->questions_per_test) {
            $required = $patient->questions()->where('is_required', true)->get();
            $slotsForOptional = max(0, $patient->questions_per_test - $required->count());
            $optional = $patient->questions()
                ->where('is_required', false)
                ->inRandomOrder()
                ->limit($slotsForOptional)
                ->get();
            $questions = $required->concat($optional);
        } else {
            $questions = $patient->questions()->orderBy('created_at')->get();
        }

        if ($patient->randomize_order) {
            $questions = $questions->shuffle();
        }

        // Guardar la transcripción del chat en el intento
        $history = Session::get('chat_history', []);
        $attempt->update(['interview_transcript' => $history]);

        return view('pages.patients.test-take', compact('patient', 'questions', 'attempt'));
    }


    /**
     * Procesa y guarda las respuestas del alumno al cuestionario.
     *
     * Flujo:
     *   1. Recupera y valida el intento activo en sesión
     *   2. Por cada respuesta: la guarda y la corrige automáticamente si procede
     *   3. Calcula la nota final (null si hay preguntas abiertas pendientes)
     *   4. Limpia la sesión y redirige al dashboard con el resultado
     *
     * Tipos de corrección:
     *   - MULTIPLE_CHOICE / TRUE_FALSE → autocorrección inmediata
     *   - OPEN_ENDED → score = null, el profesor lo revisa manualmente
     */
    public function submit(Request $request, Patient $patient)
    {
        $userId = auth()->id();
        $attemptId = Session::get('current_attempt_id');

        // Buscar el intento en sesión primero, con fallback a BD
        $attempt = null;
        if ($attemptId) {
            $attempt = TestAttempt::where('id', $attemptId)
                ->where('user_id', $userId)
                ->where('patient_id', $patient->id)
                ->first();
        }

        if (!$attempt) {
            $attempt = TestAttempt::where('user_id', $userId)
                ->where('patient_id', $patient->id)
                ->whereNull('submitted_at')
                ->latest()
                ->first();
        }

        if (!$attempt) {
            return redirect()->route('student.patients.index')
                ->with('error', 'No hay un intento activo.');
        }

        $answers = $request->input('answers', []); // array [question_id => respuesta]
        $totalScore = 0.0;
        $hasOpenEnded = false; // si hay alguna pregunta abierta, la nota queda pendiente

        foreach ($answers as $questionId => $givenAnswer) {
            $question = Question::find($questionId);

            // Seguridad: ignorar preguntas que no pertenezcan a este paciente
            if (!$question || $question->patient_id !== $patient->id) {
                continue;
            }

            $isCorrect = null;
            $score = null;
            $feedback = null;

            if ($question->question_type === Question::TYPE_OPEN_ENDED) {
                // Las preguntas abiertas no se autocorrigen; el profesor las revisará
                $hasOpenEnded = true;
            } else {
                // Comparación insensible a mayúsculas/espacios para evitar falsos negativos
                $isCorrect = strtolower(trim($givenAnswer)) === strtolower(trim($question->correct_answer));
                $score = $isCorrect ? (float) $question->points : 0.0;
                $totalScore += $score;
                $feedback = $isCorrect ? $question->feedback_correct : $question->feedback_incorrect;
            }

            Answer::create([
                'test_attempt_id' => $attempt->id,
                'question_id' => $question->id,
                'given_answer' => $givenAnswer,
                'is_correct' => $isCorrect,
                'score' => $score,
                'feedback' => $feedback,
            ]);
        }

        // Siempre marcar como enviado. La nota puede quedar null si hay preguntas abiertas.
        $attempt->update([
            'submitted_at' => now(),
            'final_score' => $hasOpenEnded ? null : round($totalScore, 2),
        ]);

        // Limpiar sesión: la simulación ha concluido
        Session::forget(['current_attempt_id', 'chat_history', 'current_ai', 'current_patient_id']);

        if ($hasOpenEnded) {
            return redirect()->route('student.dashboard')
                ->with('info', 'Cuestionario enviado. El profesor revisará las preguntas abiertas pronto.');
        }

        return redirect()->route('student.dashboard')
            ->with('success', 'Cuestionario completado. Tu nota es ' . number_format($totalScore, 2) . ' / 10.');
    }


}