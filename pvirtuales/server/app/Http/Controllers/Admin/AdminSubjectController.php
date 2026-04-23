<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Subject;
use App\Models\User;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminSubjectController extends Controller
{
    public function index(Request $request)
    {
        $perPage = in_array((int) $request->get('per_page'), [10, 20, 50, 100])
            ? (int) $request->get('per_page')
            : 10;

        $query = Subject::with('creator')
            ->withCount(['students', 'patients'])
            ->orderBy('created_at', 'desc');

        if ($request->filled('buscar')) {
            $search = $request->buscar;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%")
                    ->orWhere('institution', 'like', "%{$search}%");
            });
        }

        $teachers = User::whereIn('role_id', [Role::TEACHER_ID, Role::ADMIN_ID])
            ->orderBy('first_name')
            ->get();

        $subjects = $query->paginate($perPage)->withQueryString();
        $totalSubjects = Subject::count();
        $totalEnrollments = DB::table('subject_user')->where('role', 'student')->count();
        $totalPatients = \App\Models\Patient::count();

        if ($request->ajax()) {
            return view('pages.admin.subjects._table', compact('subjects', 'perPage'));
        }

        return view('pages.admin.subjects.index', compact(
            'subjects',
            'totalSubjects',
            'totalEnrollments',
            'totalPatients',
            'perPage',
            'teachers'
        ));
    }

    public function show(Subject $subject)
    {
        $subject->load(['creator', 'students', 'collaborators', 'patients.identity']);
        $teachers = User::where('role_id', Role::TEACHER_ID)
            ->orWhere('role_id', Role::ADMIN_ID)
            ->orderBy('first_name')
            ->get();

        return view('pages.admin.subjects.show', compact('subject', 'teachers'));
    }

    public function update(Request $request, Subject $subject)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'code' => 'nullable|string|max:50',
            'institution' => 'nullable|string|max:255',
            'created_by_user_id' => 'required|exists:users,id',
        ]);

        $subject->update($validated);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'name' => $subject->name,
                'teacher_name' => $subject->creator->full_name,
            ]);
        }

        return back()->with('success', 'Asignatura actualizada correctamente.');
    }

    public function destroy(Subject $subject)
    {
        $subject->delete();
        return redirect()->route('admin.subjects.index')
            ->with('success', 'Asignatura eliminada correctamente.');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'code' => 'required|string|max:50',
            'institution' => 'required|string|max:255',
            'created_by_user_id' => 'required|exists:users,id',
        ]);

        Subject::create($validated);

        if ($request->ajax()) {
            return response()->json(['success' => true]);
        }

        return redirect()->route('admin.subjects.index')
            ->with('success', 'Asignatura creada correctamente.');
    }

}
