<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Patient;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

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

        // --- Métricas de pacientes ---

        // Total de pacientes creados por este profesor
        $totalPatients = Patient::where('created_by_user_id', $userId)->count();

        // Pacientes publicados (disponibles para simulaciones)
        $publishedPatients = Patient::where('created_by_user_id', $userId)
            ->where('is_published', true)
            ->count();

        // Pacientes en borrador (aún no publicados)
        $draftPatients = $totalPatients - $publishedPatients;

        // --- Métricas de consultas ---
        // Total de simulaciones realizadas en pacientes de este profesor
        $totalConsultations = DB::table('test_attempts')
            ->join('patients', 'test_attempts.patient_id', '=', 'patients.id')
            ->where('patients.created_by_user_id', $userId)
            ->count();

        // --- Pacientes recientes ---
        // Los 6 últimos pacientes creados con sus relaciones para mostrar en la tabla
        $recentPatients = Patient::with(['knowledgeBase', 'subject'])
            ->where('created_by_user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->limit(6)
            ->get();

        return view('pages.teacher.dashboard', compact(
            'totalPatients',
            'publishedPatients',
            'draftPatients',
            'totalConsultations',
            'recentPatients'
        ));
    }
}