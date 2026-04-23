<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Patient;
use App\Models\User;
use App\Models\Role;
use Illuminate\Http\Request;

class AdminPatientController extends Controller
{
    public function index(Request $request)
    {
        $perPage = in_array((int) $request->get('per_page'), [10, 20, 50, 100])
            ? (int) $request->get('per_page')
            : 10;

        $query = Patient::with(['subject', 'createdBy'])
            ->withCount('testAttempts')
            ->orderBy('created_at', 'desc');

        if ($request->filled('buscar')) {
            $search = $request->buscar;
            $query->where(function ($q) use ($search) {
                $q->where('case_title', 'like', "%{$search}%")
                  ->orWhere('patient_description', 'like', "%{$search}%");
            });
        }

        if ($request->filled('estado')) {
            $query->where('is_published', $request->estado === 'publicado');
        }

        if ($request->filled('modo')) {
            $query->where('mode', $request->modo);
        }

        $patients        = $query->paginate($perPage)->withQueryString();
        $totalPatients   = Patient::count();
        $totalPublished  = Patient::where('is_published', true)->count();
        $totalDraft      = Patient::where('is_published', false)->count();

        if ($request->ajax()) {
            return view('pages.admin.patients._table', compact('patients', 'perPage'));
        }

        return view('pages.admin.patients.index', compact(
            'patients', 'totalPatients', 'totalPublished', 'totalDraft', 'perPage'
        ));
    }

    public function destroy(Patient $patient)
    {
        $patient->delete();
        return redirect()->route('admin.patients.index')
            ->with('success', 'Paciente eliminado correctamente.');
    }
}
