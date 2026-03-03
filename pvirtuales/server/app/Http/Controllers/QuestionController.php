<?php

namespace App\Http\Controllers;

use App\Models\Patient;
use App\Models\Question;
use App\Http\Requests\StoreQuestionRequest;
use Illuminate\Support\Facades\Auth;

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

        return redirect()
            ->route('teacher.patients.test', $patient)
            ->with('success', 'Pregunta eliminada.');
    }

    /**
     * Verifica que el usuario autenticado es el creador del paciente.
     * Si no, lanza un 403 Forbidden.
     */
    private function authorizeOwnership(Patient $patient): void
    {
        if ($patient->created_by_user_id !== Auth::id()) {
            abort(403, 'No tienes permiso para gestionar este paciente.');
        }
    }

    /**
     * Muestra el test al alumno para que responda las preguntas
     * tras haber completado la simulación.
     */
    public function take(Patient $patient)
    {
        $questions = $patient->questions()->orderBy('created_at')->get();

        return view('pages.patients.test-take', compact('patient', 'questions'));
    }
}