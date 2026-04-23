<?php

namespace App\Http\Controllers;

use App\Models\Patient;
use App\Http\Requests\StorePatientRequest;
use App\Http\Requests\UpdatePatientRequest;
use App\Services\PatientService;
use Illuminate\Support\Facades\Auth;
use App\Models\Subject;

/**
 * Gestiona el CRUD de Pacientes Virtuales (Casos Clínicos).
 *
 * Permite a los profesores crear, previsualizar, publicar y eliminar
 * pacientes virtuales que luego serán usados en simulaciones.
 *
 * FLUJO DE CREACIÓN:
 *   1. selectMode()     → Elige entre Modo Básico o Avanzado
 *   2. createBasic()    → Formulario guiado de 5 secciones en scroll
 *      createAdvanced() → Formulario wizard de 5 pasos
 *   3. store()          → Ambos modos envían aquí → PatientService → prompt generado
 *   4. preview()        → Revisa el prompt Markdown generado
 *   5. publish()        → Publica para que esté disponible en simulaciones
 */
class PatientController extends Controller
{
    protected PatientService $patientService;

    public function __construct(PatientService $patientService)
    {
        $this->patientService = $patientService;
    }

    /**
     * Muestra el listado de pacientes creados por el profesor autenticado.
     * Carga 'subject' para mostrar la asignatura en la tabla sin N+1 queries.
     */
    public function index()
    {
        $patients = Patient::with(['prompt', 'knowledgeBase', 'subject'])
            ->where('created_by_user_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->get();

        return view('pages.teacher.patients.index', compact('patients'));
    }

    /**
     * Muestra la página de selección de modo (Básico / Avanzado).
     */
    public function selectMode()
    {
        return view('pages.patients.select-mode');
    }

    /**
     * Muestra el formulario de creación en Modo Básico.
     */
    public function createBasic()
    {
        session()->forget('_old_input');
        $subjects = Auth::user()->isAdmin()
            ? Subject::orderBy('name')->get()
            : Subject::where('created_by_user_id', Auth::id())->orderBy('name')->get();

        return view('pages.patients.create-basic', compact('subjects'));
    }


    /**
     * Muestra el formulario de creación en Modo Avanzado.
     */
    public function createAdvanced()
    {
        session()->forget('_old_input'); // <- añadir
        return view('pages.patients.create-advanced');
    }

    /**
     * Almacena un nuevo paciente y genera su prompt automáticamente.
     * Tras guardar, redirige a la previsualización del prompt.
     */
    public function store(StorePatientRequest $request)
    {
        $patient = $this->patientService->createPatient(
            $request->validated(),
            Auth::id()
        );

        $origen = request('origen');
        $url = route('teacher.patients.preview', $patient) . ($origen ? '?origen=' . $origen : '');
        return redirect($url)->with('success', 'Paciente creado exitosamente.');

    }

    /**
     * Muestra la previsualización del prompt generado para un paciente.
     * Solo el creador del paciente puede verlo.
     */
    public function preview(Patient $patient)
    {
        if ($patient->created_by_user_id !== Auth::id() && !Auth::user()->isAdmin()) {
            abort(403, 'No tienes permiso para ver este paciente.');
        }

        $patient->load(['prompt', 'knowledgeBase']);

        return view('pages.patients.preview', compact('patient'));
    }

    /**
     * Publica un paciente para que esté disponible en simulaciones.
     * Requiere tener al menos 1 pregunta en el test antes de publicar.
     */
    public function publish(Patient $patient)
    {
        if ($patient->created_by_user_id !== Auth::id() && !Auth::user()->isAdmin()) {
            abort(403);
        }

        $origen = request('origen');

        // DESPUBLICAR — sin validaciones
        if ($patient->is_published) {
            $patient->update(['is_published' => false]);
            $url = route('teacher.patients.preview', $patient) . ($origen ? '?origen=' . $origen : '');
            return redirect($url)->with('success', 'Paciente despublicado. Ya no está disponible para consultas.');

        }

        // PUBLICAR — con validaciones existentes
        if (!$patient->hasTest()) {
            return redirect()
                ->route('teacher.patients.test', $patient)
                ->with('error', 'Debes crear al menos 1 pregunta en el test antes de publicar.');
        }

        $patient->update(['is_published' => true]);

        $url = route('teacher.patients.preview', $patient) . ($origen ? '?origen=' . $origen : '');
        return redirect($url)->with('success', 'Paciente publicado. Ya está disponible para consultas.');
    }



    public function edit(Patient $patient)
    {
        if ($patient->created_by_user_id !== Auth::id() && !Auth::user()->isAdmin())
            abort(403);

        $patient->load(['identity', 'psychology', 'knowledgeBase', 'conversationLogic', 'coherenceExamples']);

        $subjects = Auth::user()->isAdmin()
            ? Subject::orderBy('name')->get()
            : Subject::where('created_by_user_id', Auth::id())->orderBy('name')->get();

        // Pre-rellena old() para que los partials funcionen sin cambios.
        session()->put('_old_input', $this->patientService->extractFormData($patient));

        $view = $patient->mode === 'basic' ? 'pages.patients.edit-basic' : 'pages.patients.edit-advanced';
        return view($view, compact('patient', 'subjects'));
    }

    public function update(UpdatePatientRequest $request, Patient $patient)
    {
        if ($patient->created_by_user_id !== Auth::id() && !Auth::user()->isAdmin())
            abort(403);

        $this->patientService->updatePatient($request->validated(), $patient);

        $origen = request('origen');
        $url = route('teacher.patients.preview', $patient) . ($origen ? '?origen=' . $origen : '');
        return redirect($url)->with('success', 'Paciente actualizado. El prompt ha sido regenerado.');

    }


    /**
     * Elimina un paciente y todos sus datos relacionados.
     * Solo el creador del paciente puede eliminarlo.
     */
    public function destroy(Patient $patient, $origen)
    {
        if ($patient->created_by_user_id !== Auth::id() && !Auth::user()->isAdmin()) {
            abort(403);
        }

        if ($origen === 'dashboard') {
            $redirectRoute = route('teacher.dashboard');
        } else if ($origen === 'index') {

            $redirectRoute = route('teacher.patients.index');
        } else {
            $redirectRoute = route('teacher.patients.preview', $patient);
        }

        $patient->delete();

        return redirect()
            ->route('teacher.patients.index')
            ->with('success', 'Paciente eliminado correctamente.');
    }
}