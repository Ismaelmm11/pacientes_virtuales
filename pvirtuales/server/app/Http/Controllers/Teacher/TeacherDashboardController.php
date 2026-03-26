<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Patient;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Subject;

/**
 * Controlador del Dashboard del Profesor.
 *
 * Recoge las métricas y los datos recientes del profesor autenticado
 * para mostrarlos en su página de inicio tras el login.
 *
 * DATOS QUE RECOGE:
 *   - Total de pacientes creados por este profesor
 *   - Cuántos están publicados vs en borrador
 *   - Total de consultas recibidas en sus pacientes
 *   - Lista de los 6 pacientes más recientes con su estado
 */
class TeacherDashboardController extends Controller
{
    public function index()
    {
        $userId = Auth::id();

        $totalPatients = Patient::where('created_by_user_id', $userId)->count();

        $publishedPatients = Patient::where('created_by_user_id', $userId)
            ->where('is_published', true)->count();

        $draftPatients = $totalPatients - $publishedPatients;

        $totalConsultations = DB::table('test_attempts')
            ->join('patients', 'test_attempts.patient_id', '=', 'patients.id')
            ->where('patients.created_by_user_id', $userId)
            ->count();

        $recentPatients = Patient::with(['knowledgeBase', 'subject'])
            ->where('created_by_user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->limit(6)
            ->get();

        $totalSubjects = Subject::where('created_by_user_id', $userId)->count();

        $totalStudents = DB::table('subject_user')
            ->join('subjects', 'subject_user.subject_id', '=', 'subjects.id')
            ->where('subjects.created_by_user_id', $userId)
            ->where('subject_user.role', 'student')
            ->distinct('subject_user.user_id')
            ->count('subject_user.user_id');

        $recentSubjects = Subject::with(['students', 'patients'])
            ->where('created_by_user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->limit(4)
            ->get();

        // Actividad reciente: últimas 5 simulaciones en los pacientes del profesor
        $patientIds = Patient::where('created_by_user_id', $userId)->pluck('id');

        $recentActivity = \App\Models\TestAttempt::with(['patient', 'user'])
            ->whereIn('patient_id', $patientIds)
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        // Tests enviados pendientes de corrección manual (preguntas abiertas)
        $pendingGrading = \App\Models\TestAttempt::with(['patient', 'user'])
            ->whereIn('patient_id', $patientIds)
            ->whereNotNull('submitted_at')
            ->whereNull('final_score')
            ->orderBy('submitted_at', 'desc')
            ->limit(5)
            ->get();

        return view('pages.teacher.dashboard', compact(
            'totalPatients',
            'publishedPatients',
            'draftPatients',
            'totalSubjects',
            'totalStudents',
            'recentSubjects',
            'totalConsultations',
            'recentPatients',
            'recentActivity',
            'pendingGrading',
        ));
    }

}