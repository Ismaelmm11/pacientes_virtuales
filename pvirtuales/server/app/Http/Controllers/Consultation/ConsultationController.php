<?php

namespace App\Http\Controllers\Consultation;

use App\Http\Controllers\Controller;
use App\Models\Patient;

/**
 * Gestiona el panel de selección de consultas.
 * 
 * Muestra los pacientes disponibles junto con los proveedores de IA
 * configurados para que el usuario inicie una simulación.
 */
class ConsultationController extends Controller
{
    /**
     * Muestra el dashboard de consultas con los pacientes y proveedores de IA disponibles.
     *
     * @return \Illuminate\View\View
     */
    public function dashboard()
    {
        $patients = Patient::with(['type', 'prompt', 'knowledgeBase'])
            ->where('is_published', true)
            ->get();

        return view('pages.consultation.dashboard', compact('patients'));
    }
}