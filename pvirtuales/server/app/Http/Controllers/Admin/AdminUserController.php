<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Role;
use Illuminate\Http\Request;
use App\Models\Subject;
use App\Models\Patient;


class AdminUserController extends Controller
{
    public function index(Request $request)
    {
        $perPage = in_array((int) $request->get('per_page'), [10, 20, 50, 100])
            ? (int) $request->get('per_page')
            : 10;

        $query = User::with('role')->orderBy('created_at', 'desc');

        if ($request->filled('rol')) {
            $query->where('role_id', $request->rol);
        }

        if ($request->filled('buscar')) {
            $search = $request->buscar;
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $users = $query->paginate($perPage)->withQueryString();
        $totalTeachers = User::where('role_id', Role::TEACHER_ID)->count();
        $totalStudents = User::where('role_id', Role::STUDENT_ID)->count();
        $totalAdmins = User::where('role_id', Role::ADMIN_ID)->count();

        if ($request->ajax()) {
            return view('pages.admin.users._table', compact('users', 'perPage'));
        }

        return view('pages.admin.users.index', compact(
            'users',
            'totalTeachers',
            'totalStudents',
            'totalAdmins',
            'perPage'
        ));
    }

    public function show(User $user)
    {
        $subjects = collect();
        $patients = collect();
        $enrolledSubjects = collect();
        $testAttempts = collect();

        if ($user->isTeacher() || $user->isAdmin()) {
            $subjects = Subject::where('created_by_user_id', $user->id)
                ->withCount('patients')
                ->get();
            $patients = Patient::where('created_by_user_id', $user->id)
                ->with('subject')
                ->withCount('testAttempts')
                ->latest()
                ->get();
        } else {
            $enrolledSubjects = $user->enrolledSubjects;
            $testAttempts = $user->testAttempts()->with('patient')->latest()->get();
        }

        return view('pages.admin.users.show', compact(
            'user',
            'subjects',
            'patients',
            'enrolledSubjects',
            'testAttempts'
        ));
    }

    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:150',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'role_id' => 'required|in:1,2,3',
        ]);

        $user->update($validated);

        if ($request->ajax()) {
            return response()->json(['success' => true, 'full_name' => $user->fresh()->full_name]);
        }

        return back()->with('success', 'Usuario actualizado correctamente.');
    }

    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'No puedes eliminar tu propio usuario.');
        }

        $user->delete();
        return redirect()->route('admin.users.index')->with('success', 'Usuario eliminado correctamente.');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:150',
            'email' => 'required|email|unique:users,email',
            'role_id' => 'required|in:1,2,3',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $validated['password'] = bcrypt($validated['password']);
        $validated['auth_provider'] = 'local';

        User::create($validated);

        if ($request->ajax()) {
            return response()->json(['success' => true]);
        }

        return redirect()->route('admin.users.index')->with('success', 'Usuario creado correctamente.');
    }


}
