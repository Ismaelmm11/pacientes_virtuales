<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Answer;
use App\Models\Patient;
use App\Models\TestAttempt;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Question;
use Illuminate\Support\Str;


class TeacherFollowupController extends Controller
{
    private function teacherPatientIds(): array
    {
        return Patient::where('created_by_user_id', Auth::id())
            ->pluck('id')
            ->toArray();
    }

    public function consultations()
    {
        $patientIds = $this->teacherPatientIds();

        $attempts = TestAttempt::with(['patient.subject', 'user'])
            ->whereIn('patient_id', $patientIds)
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        $totalCount = TestAttempt::whereIn('patient_id', $patientIds)->count();
        $pendingCount = TestAttempt::whereIn('patient_id', $patientIds)->whereNull('submitted_at')->whereNotNull('interview_transcript')->count();
        $gradingCount = TestAttempt::whereIn('patient_id', $patientIds)->whereNotNull('submitted_at')->whereNull('final_score')->count();
        $completedCount = TestAttempt::whereIn('patient_id', $patientIds)->whereNotNull('final_score')->count();

        return view('pages.teacher.consultations.index', compact(
            'attempts',
            'totalCount',
            'pendingCount',
            'gradingCount',
            'completedCount'
        ));
    }

    public function results()
    {
        // Solo pacientes de examen creados por este profesor
        $examPatients = Patient::with(['subject'])
            ->where('created_by_user_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($patient) {

                // Intentos con el test enviado
                $patient->submitted_count = TestAttempt::where('patient_id', $patient->id)
                    ->whereNotNull('submitted_at')
                    ->count();

                // Alumnos matriculados en la asignatura de este paciente
                $enrolledCount = $patient->subject->students()->count();

                // Total posible = intentos máximos × alumnos matriculados
                // Si el paciente es ilimitado (-1), no tiene sentido calcular un total
                $patient->total_possible = $patient->max_attempts === -1
                    ? null
                    : $patient->max_attempts * $enrolledCount;

                // ¿Hay tests enviados sin nota? (preguntas abiertas sin corregir)
                $patient->pending_grading = TestAttempt::where('patient_id', $patient->id)
                    ->whereNotNull('submitted_at')
                    ->whereNull('final_score')
                    ->count();

                // Nota media: solo se calcula si los resultados están publicados
                $patient->avg_grade = $patient->results_published
                    ? TestAttempt::where('patient_id', $patient->id)
                        ->whereNotNull('final_score')
                        ->avg('final_score')
                    : null;

                return $patient;
            });

        return view('pages.teacher.results.index', compact('examPatients'));
    }


    /**
     * Vista de detalle de resultados de un paciente de examen.
     * Muestra dos tabs: alumnos que han entregado y alumnos que no.
     */
    public function showPatientResults(Patient $patient)
    {
        // Solo el creador puede ver esto
        if ($patient->created_by_user_id !== Auth::id()) {
            abort(403);
        }

        // Intentos enviados con sus datos calculados
        $attempts = TestAttempt::with(['user', 'answers.question'])
            ->where('patient_id', $patient->id)
            ->whereNotNull('submitted_at')
            ->orderBy('submitted_at', 'desc')
            ->get()
            ->map(function ($attempt) {
                // Tiempo de consulta en minutos (created_at = inicio simulación, submitted_at = entrega test)
                $attempt->duration_minutes = $attempt->created_at && $attempt->submitted_at
                    ? (int) $attempt->created_at->diffInMinutes($attempt->submitted_at)
                    : null;

                // Número de mensajes escritos por el alumno en la consulta
                $transcript = $attempt->interview_transcript ?? [];
                $attempt->student_messages = collect($transcript)
                    ->where('role', 'user')
                    ->count();

                return $attempt;
            });

        // Alumnos matriculados que NO han enviado ningún intento
        $enrolledStudents = $patient->subject->students()->get();
        $studentsWithSubmission = $attempts->pluck('user_id')->unique();
        $studentsWithoutSubmission = $enrolledStudents->whereNotIn('id', $studentsWithSubmission);

        // --- Stats para las tarjetas ---

        // Ratio de entregas: alumnos que han entregado / total matriculados
        $enrolledCount = $enrolledStudents->count();
        $submittedCount = $studentsWithSubmission->count();
        $deliveryRatio = $enrolledCount > 0 ? round(($submittedCount / $enrolledCount) * 100) : 0;

        // Tiempo medio de consulta (en minutos, solo los que tienen ambas fechas)
        $avgDuration = $attempts
            ->whereNotNull('duration_minutes')
            ->avg('duration_minutes');
        $avgDurationFormatted = $avgDuration !== null
            ? round($avgDuration) . ' min'
            : '—';

        // Nota media (solo si resultados publicados)
        $avgGrade = $patient->results_published
            ? $attempts->whereNotNull('final_score')->avg('final_score')
            : null;

        // Tasa de aprobados (nota >= 5, solo si publicado)
        $passCount = $patient->results_published
            ? $attempts->where('final_score', '>=', 5)->count()
            : null;


        // ---------------------------------------------------------------
// Datos para las 6 gráficas de analíticas
// Solo se calculan cuando los resultados están publicados
// ---------------------------------------------------------------
        if ($patient->results_published) {

            // Gráfica 1: Nota por alumno
            $chartGrades = $attempts->whereNotNull('final_score')->map(fn($a) => [
                'name' => $a->user?->full_name ?? 'Alumno',
                'score' => (float) $a->final_score,
            ])->values();

            // Gráfica 2: Distribución de notas en 5 rangos fijos
            $dist = ['0–2' => 0, '2–4' => 0, '4–6' => 0, '6–8' => 0, '8–10' => 0];
            foreach ($attempts->whereNotNull('final_score') as $a) {
                $s = (float) $a->final_score;
                if ($s < 2)
                    $dist['0–2']++;
                elseif ($s < 4)
                    $dist['2–4']++;
                elseif ($s < 6)
                    $dist['4–6']++;
                elseif ($s < 8)
                    $dist['6–8']++;
                else
                    $dist['8–10']++;
            }
            $chartDistribution = $dist;

            // Gráfica 3: Tasa de error por pregunta
            // Para OPEN_ENDED: incorrecto si score <= puntos_máximos / 2
            $qStats = [];
            foreach ($attempts as $attempt) {
                foreach ($attempt->answers as $answer) {
                    $qId = $answer->question_id;
                    if (!isset($qStats[$qId])) {
                        $qStats[$qId] = [
                            'text' => Str::limit($answer->question->question_text, 55),
                            'total' => 0,
                            'incorrect' => 0,
                        ];
                    }
                    $qStats[$qId]['total']++;

                    $isCorrect = $answer->question->question_type === 'OPEN_ENDED'
                        ? ($answer->score !== null && (float) $answer->score > ((float) ($answer->question->points ?? 10) / 2))
                        : (bool) $answer->is_correct;

                    if (!$isCorrect)
                        $qStats[$qId]['incorrect']++;
                }
            }
            $chartQuestionErrors = collect($qStats)
                ->map(fn($q) => [
                    'text' => $q['text'],
                    'pct' => $q['total'] > 0 ? round(($q['incorrect'] / $q['total']) * 100) : 0,
                ])
                ->sortByDesc('pct')
                ->values();

            // Gráfica 4: Tiempo de consulta por alumno (en minutos)
            $chartTimes = $attempts->whereNotNull('duration_minutes')->map(fn($a) => [
                'name' => $a->user?->full_name ?? 'Alumno',
                'minutes' => $a->duration_minutes,
            ])->values();

            // Gráfica 5: Scatter — mensajes enviados (X) vs nota final (Y)
            $chartScatter = $attempts->whereNotNull('final_score')->map(fn($a) => [
                'x' => $a->student_messages,
                'y' => (float) $a->final_score,
                'name' => $a->user?->full_name ?? 'Alumno',
            ])->values();

            // Gráfica 6: Heatmap pregunta × alumno (1 = correcto, 0 = incorrecto)
            // Recopilar las preguntas únicas que aparecen en los intentos
            $allQuestions = [];
            foreach ($attempts as $attempt) {
                foreach ($attempt->answers as $answer) {
                    if (!isset($allQuestions[$answer->question_id])) {
                        $allQuestions[$answer->question_id] = [
                            'text' => Str::limit($answer->question->question_text, 55), // para gráfica 3 (errores)
                            'fullText' => $answer->question->question_text,                  // ← texto completo para tooltip heatmap
                            'points' => (float) ($answer->question->points ?? 10),
                            'type' => $answer->question->question_type,
                        ];
                    }
                }
            }
            // Cada serie del heatmap = una pregunta; cada punto = un alumno
            // Heatmap: series con nombre "Pregunta N" + array separado de textos completos para tooltip
            $chartHeatmap = [];
            $heatmapLabels = []; // Textos completos en el mismo orden que las series
            $qIndex = 0;

            foreach ($allQuestions as $qId => $qData) {
                $qIndex++;
                $heatmapLabels[] = $qData['fullText']; // texto sin truncar para el tooltip

                $serie = ['name' => 'Pregunta ' . $qIndex, 'data' => []]; // ← etiqueta corta en el eje Y

                foreach ($attempts as $attempt) {
                    $answer = $attempt->answers->firstWhere('question_id', $qId);
                    if (!$answer) {
                        $y = 0;
                    } elseif ($qData['type'] === 'OPEN_ENDED') {
                        $y = ($answer->score !== null && (float) $answer->score > ($qData['points'] / 2)) ? 1 : 0;
                    } else {
                        $y = $answer->is_correct ? 1 : 0;
                    }
                    $serie['data'][] = ['x' => $attempt->user?->full_name ?? 'Alumno', 'y' => $y];
                }
                $chartHeatmap[] = $serie;
            }

        } else {
            // Si no están publicados los resultados, arrays vacíos (la tab no aparece en la vista)
            $chartGrades = $chartDistribution = $chartQuestionErrors = $chartTimes = $chartScatter = $chartHeatmap = $heatmapLabels = [];
        }


        return view('pages.teacher.results.patient', compact(
            'patient',
            'attempts',
            'studentsWithoutSubmission',
            'enrolledCount',
            'submittedCount',
            'deliveryRatio',
            'avgDurationFormatted',
            'avgGrade',
            'passCount',
            // Gráficas de analíticas (vacías si resultados no publicados)
            'chartGrades',
            'chartDistribution',
            'chartQuestionErrors',
            'chartTimes',
            'chartScatter',
            'chartHeatmap',
            'heatmapLabels',
        ));
    }


    public function showResult(TestAttempt $attempt)
    {
        $this->authorizeAttempt($attempt);
        $attempt->load(['patient.subject', 'user', 'answers.question']);

        // Duración: desde inicio de la simulación hasta envío del test
        $durationMinutes = $attempt->created_at && $attempt->submitted_at
            ? (int) $attempt->created_at->diffInMinutes($attempt->submitted_at)
            : null;

        // Mensajes enviados por el alumno en la consulta
        $transcript = $attempt->interview_transcript ?? [];
        $studentMessages = collect($transcript)->where('role', 'user')->count();

        // Aciertos sobre el total (solo preguntas con corrección automática)
        $correctCount = $attempt->answers->where('is_correct', 1)->count();
        $totalAnswered = $attempt->answers->count();

        return view('pages.teacher.results.show', compact(
            'attempt',
            'durationMinutes',
            'studentMessages',
            'correctCount',
            'totalAnswered',
        ));
    }

    public function grade(Request $request, TestAttempt $attempt)
    {
        $this->authorizeAttempt($attempt);

        $scores = $request->input('scores', []);    // [answer_id => puntuación]
        $feedbacks = $request->input('feedbacks', []); // [answer_id => feedback del profesor]

        // Actualizar las respuestas abiertas con la puntuación y feedback del profesor
        foreach ($scores as $answerId => $score) {
            $answer = Answer::find($answerId);
            if (!$answer || $answer->test_attempt_id !== $attempt->id)
                continue;

            $maxPoints = (float) ($answer->question->points ?? 10);
            $score = max(0, min((float) $score, $maxPoints));

            $answer->update([
                'score' => $score,
                'is_correct' => $score > 0,
                'feedback' => $feedbacks[$answerId] ?? null,
            ]);
        }

        // Recalcular nota final normalizada sobre 10
        // Fórmula: (suma de scores obtenidos / suma de puntos máximos posibles) × 10
        $answersWithQ = $attempt->answers()->with('question')->get();
        $totalScore = $answersWithQ->sum('score');
        $maxPossible = $answersWithQ->sum(fn($a) => (float) ($a->question->points ?? 10));
        $finalScore = $maxPossible > 0 ? round(($totalScore / $maxPossible) * 10, 2) : 0;

        $attempt->update([
            'final_score' => $finalScore,
            'general_feedback' => $request->input('general_feedback'),
        ]);

        return redirect()
            ->route('teacher.results.show', $attempt)
            ->with('success', 'Corrección guardada. Nota final: ' . number_format($finalScore, 2) . ' / 10.');
    }

    /**
     * Corrige una sola respuesta abierta vía AJAX.
     * Devuelve JSON con la puntuación guardada y, si todas las preguntas
     * abiertas ya están corregidas, la nota final recalculada del intento.
     */
    public function gradeAnswer(Request $request, TestAttempt $attempt, Answer $answer)
    {
        $this->authorizeAttempt($attempt);

        // Verificar que la respuesta pertenece a este intento y no a otro
        if ($answer->test_attempt_id !== $attempt->id) {
            return response()->json(['error' => 'Respuesta no válida'], 403);
        }

        // Solo se permiten corregir preguntas abiertas (las otras se autocorrigen)
        if ($answer->question->question_type !== Question::TYPE_OPEN_ENDED) {
            return response()->json(['error' => 'Solo se pueden corregir preguntas abiertas'], 422);
        }

        // Clamp: la puntuación no puede salirse del rango [0, puntos_máximos]
        $maxPoints = (float) ($answer->question->points ?? 10);
        $score = max(0, min((float) $request->input('score', 0), $maxPoints));
        $feedback = $request->input('feedback', '');

        $answer->update([
            'score' => $score,
            'is_correct' => $score > 0,
            'feedback' => $feedback,
        ]);

        // Recalcular nota final: (suma scores / puntos posibles totales) × 10
        $answersWithQ = $attempt->answers()->with('question')->get();
        $totalScore = $answersWithQ->sum('score');
        $maxPossible = $answersWithQ->sum(fn($a) => (float) ($a->question->points ?? 10));
        $finalScore = $maxPossible > 0 ? round(($totalScore / $maxPossible) * 10, 2) : 0;

        // Comprobar si quedan preguntas abiertas sin puntuar
        $pendingOpen = $attempt->answers()
            ->whereHas('question', fn($q) => $q->where('question_type', Question::TYPE_OPEN_ENDED))
            ->whereNull('score')
            ->count();

        // Solo guardar la nota final cuando ya no queden abiertas pendientes
        if ($pendingOpen === 0) {
            $attempt->update(['final_score' => $finalScore]);
        }

        return response()->json([
            'score' => $score,
            'feedback' => $feedback,
            'final_score' => $pendingOpen === 0 ? $finalScore : null,
            'all_graded' => $pendingOpen === 0,
        ]);
    }


    /**
     * Publica los resultados de un paciente para que los alumnos puedan verlos.
     * Solo se permite si todos los intentos enviados tienen nota final (final_score NOT NULL).
     * Si hay preguntas abiertas sin corregir, el profesor no puede publicar.
     */
    public function publishResults(Patient $patient)
    {
        // Solo el creador del paciente puede publicar sus resultados
        if ($patient->created_by_user_id !== Auth::id()) {
            abort(403);
        }

        // Comprobar si hay intentos enviados pero sin nota final (preguntas abiertas sin corregir)
        $pendingCount = TestAttempt::where('patient_id', $patient->id)
            ->whereNotNull('submitted_at')
            ->whereNull('final_score')
            ->count();

        if ($pendingCount > 0) {
            return redirect()
                ->back()
                ->with('error', "No puedes publicar los resultados: hay {$pendingCount} test(s) con preguntas abiertas sin corregir.");
        }

        // Al publicar: marcar resultados como visibles Y cerrar el paciente
        // para que nadie más pueda iniciar una nueva consulta
        $patient->update([
            'results_published' => true,
            'is_published' => false,
        ]);

        return redirect()
            ->back()
            ->with('success', 'Resultados publicados. Los alumnos ya pueden ver sus notas.');
    }

    /**
     * Despublica los resultados de un paciente.
     * Vuelve a abrir el paciente para que los alumnos puedan consultarlo.
     */
    public function unpublishResults(Patient $patient)
    {
        if ($patient->created_by_user_id !== Auth::id()) {
            abort(403);
        }

        // Al despublicar: ocultar resultados y reabrir el paciente
        $patient->update([
            'results_published' => false,
            'is_published' => true,
        ]);

        return redirect()
            ->back()
            ->with('success', 'Resultados despublicados. El examen está disponible de nuevo para consultas.');
    }




    private function authorizeAttempt(TestAttempt $attempt): void
    {
        if (!in_array($attempt->patient_id, $this->teacherPatientIds())) {
            abort(403);
        }
    }
}
