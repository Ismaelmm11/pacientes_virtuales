<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Patient;
use App\Models\TestAttempt;
use Illuminate\Support\Facades\Auth;

/**
 * Controlador del Dashboard del Alumno.
 *
 * Recoge métricas y datos personales del alumno autenticado
 * para mostrar su progreso en la página de inicio.
 *
 * DATOS QUE RECOGE:
 *   - Asignaturas en las que está matriculado
 *   - Pacientes publicados disponibles en sus asignaturas
 *   - Simulaciones realizadas (total de test_attempts)
 *   - Nota media de los tests completados
 *   - Pacientes disponibles para practicar (con intentos restantes)
 *   - Actividad reciente (últimas 5 simulaciones)
 *   - Tests pendientes (simulaciones sin nota final)
 *   - Resultados completados (con nota)
 *   - Lista de asignaturas matriculadas
 */
class StudentDashboardController extends Controller
{
    public function index()
    {
        $userId = Auth::id();
        $user = Auth::user();

        // IDs de las asignaturas en las que el alumno está matriculado
        $subjectIds = $user->enrolledSubjects()->pluck('subjects.id');

        // --- Métricas (tarjetas superiores) ---

        // Total de asignaturas en las que está matriculado
        $enrolledSubjectsCount = $subjectIds->count();

        // Total de pacientes publicados accesibles para él
        $availablePatientsCount = Patient::whereIn('subject_id', $subjectIds)
            ->where('is_published', 1)
            ->count();

        // Total de simulaciones realizadas (independientemente de si tienen nota o no)
        $simulationsCount = TestAttempt::where('user_id', $userId)->count();

        // Nota media entre todos los tests que ha completado (pueden ser null si no ha hecho ninguno)
        $avgGrade = TestAttempt::where('user_id', $userId)
            ->whereNotNull('final_score')
            ->avg('final_score');

        // --- Pacientes disponibles para practicar ---
        // Publicados en sus asignaturas y con al menos un intento restante
        $availablePatients = Patient::with('subject')
            ->whereIn('subject_id', $subjectIds)
            ->where('is_published', 1)
            ->get()
            ->map(function ($patient) use ($userId) {
                // Cuántos intentos ha consumido este alumno en este paciente
                $patient->attempts_used = TestAttempt::where('user_id', $userId)
                    ->where('patient_id', $patient->id)
                    ->count();
                return $patient;
            })
            ->filter(fn($patient) => $patient->max_attempts === -1 || $patient->attempts_used < $patient->max_attempts)
            ->values();

        // --- Actividad reciente ---
        // Últimas 5 simulaciones del alumno ordenadas por fecha descendente
        $recentActivity = TestAttempt::with(['patient.subject'])
            ->where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        // --- Tests pendientes ---
        // Solo aparecen si el alumno terminó la simulación y aún no ha enviado el test.
        // whereNotNull('interview_transcript') filtra los intentos que están en mitad de la consulta.
        $pendingTests = TestAttempt::with(['patient.subject'])
            ->where('user_id', $userId)
            ->whereNull('submitted_at')
            ->whereNotNull('interview_transcript')
            ->orderBy('created_at', 'desc')
            ->get()
            ->unique('patient_id')
            ->values();


        // --- Resultados completados ---
        // Últimos 5 tests con nota final asignada
        $completedTests = TestAttempt::with(['patient.subject'])
            ->where('user_id', $userId)
            ->whereNotNull('submitted_at')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        // --- Asignaturas matriculadas ---
        // Con conteo de pacientes publicados por asignatura para mostrar en la tabla
        $enrolledSubjects = $user->enrolledSubjects()
            ->withCount(['patients as available_patients_count' => fn($q) => $q->where('is_published', 1)])
            ->get();

        return view('pages.student.dashboard', compact(
            'enrolledSubjectsCount',
            'availablePatientsCount',
            'simulationsCount',
            'avgGrade',
            'availablePatients',
            'recentActivity',
            'pendingTests',
            'completedTests',
            'enrolledSubjects',
        ));
    }

    /**
     * Lista completa de pacientes disponibles para practicar.
     * Accesible desde el enlace "Practicar" del sidebar.
     * Redirige al dashboard por ahora — se puede implementar una vista dedicada más adelante.
     */
    /**
     * Lista completa de pacientes a los que el alumno tiene acceso.
     * Muestra todos los publicados en sus asignaturas, agrupados por asignatura,
     * indicando si aún puede simular o ha agotado los intentos.
     */
    public function patients()
    {
        $userId = Auth::id();
        $user = Auth::user();

        // IDs de las asignaturas del alumno
        $subjectIds = $user->enrolledSubjects()->pluck('subjects.id');

        // Todos los pacientes publicados, con intentos usados calculados
        $patients = Patient::with('subject')
            ->whereIn('subject_id', $subjectIds)
            ->where('is_published', 1)
            ->orderBy('subject_id')
            ->orderBy('case_title')
            ->get()
            ->map(function ($patient) use ($userId) {
                $patient->attempts_used = TestAttempt::where('user_id', $userId)
                    ->where('patient_id', $patient->id)
                    ->count();
                return $patient;
            });

        // Agrupados por asignatura para organizar la vista en secciones
        $patientsBySubject = $patients->groupBy('subject_id');

        // Asignaturas del alumno (para el encabezado de cada sección)
        $enrolledSubjects = $user->enrolledSubjects()
            ->whereIn('subjects.id', $subjectIds)
            ->get()
            ->keyBy('id');

        return view('pages.student.patients', compact(
            'patientsBySubject',
            'enrolledSubjects',
        ));
    }

    /**
     * Asignaturas en las que está matriculado el alumno.
     * Muestra el detalle de cada asignatura con sus pacientes disponibles.
     */
    public function subjects()
    {
        $user = Auth::user();
        $userId = Auth::id();

         $completedTests = TestAttempt::with(['patient.subject'])
            ->where('user_id', $userId)
            ->whereNotNull('submitted_at')
            ->orderBy('created_at', 'desc')
            ->get();

        $subjects = $user->enrolledSubjects()
            ->withCount(['patients as total_patients' => fn($q) => $q->where('is_published', 1)])
            ->get()
            ->map(function ($subject) use ($userId) {
                // Pacientes publicados de esta asignatura
                $subject->patients_list = Patient::with([])
                    ->where('subject_id', $subject->id)
                    ->where('is_published', 1)
                    ->orderBy('case_title')
                    ->get()
                    ->map(function ($patient) use ($userId) {
                    $patient->attempts_used = TestAttempt::where('user_id', $userId)
                        ->where('patient_id', $patient->id)
                        ->count();
                    $patient->completed_attempts = TestAttempt::where('user_id', $userId)
                        ->where('patient_id', $patient->id)
                        ->whereNotNull('final_score')
                        ->count();
                    return $patient;
                });
                return $subject;
            });

        $total = $completedTests->count();
        $totalPatients = $subjects->sum('total_patients');
        $totalCompleted = TestAttempt::where('user_id', $userId)->whereNotNull('final_score')->count();

        return view('pages.student.subjects', compact('subjects', 'totalPatients', 'totalCompleted', 'total'));
    }

    /**
     * Historial completo de consultas del alumno.
     * Muestra todos los TestAttempts (en curso, pendientes de test y completados).
     */
    public function consultations()
    {
        $userId = Auth::id();

        $attempts = TestAttempt::with(['patient.subject'])
            ->where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->get();

        $totalCount = $attempts->count();
        $pendingCount   = $attempts->whereNull('submitted_at')->whereNotNull('interview_transcript')->count();
        $gradingCount   = $attempts->whereNotNull('submitted_at')->whereNull('final_score')->count();
        $completedCount = $attempts->whereNotNull('final_score')->count();

        return view('pages.student.consultations', compact(
            'attempts',
            'totalCount',
            'pendingCount',
            'gradingCount',
            'completedCount'
        ));
    }

    /**
     * Resultados de todos los tests completados del alumno.
     */
    public function results()
    {
        $userId = Auth::id();

        $completedTests = TestAttempt::with(['patient.subject'])
            ->where('user_id', $userId)
            ->whereNotNull('submitted_at')
            ->orderBy('created_at', 'desc')
            ->get();


        $avgGrade = $completedTests->avg('final_score');
        $bestGrade = $completedTests->max('final_score');
        $passCount = $completedTests->where('final_score', '>=', 50)->count();
        $total = $completedTests->count();

        return view('pages.student.results', compact(
            'completedTests',
            'avgGrade',
            'bestGrade',
            'passCount',
            'total'
        ));
    }

}
