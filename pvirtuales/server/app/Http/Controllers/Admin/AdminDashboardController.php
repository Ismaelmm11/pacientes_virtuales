<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Subject;
use App\Models\Patient;
use App\Models\TestAttempt;
use App\Models\Role;

class AdminDashboardController extends Controller
{
    public function index()
    {
        $totalTeachers    = User::where('role_id', Role::TEACHER_ID)->count();
        $totalStudents    = User::where('role_id', Role::STUDENT_ID)->count();
        $totalSubjects    = Subject::count();
        $totalPatients    = Patient::count();
        $totalSimulations = TestAttempt::count();
        $pendingGrading   = TestAttempt::whereNotNull('submitted_at')
                                ->whereNull('final_score')
                                ->count();

        $recentSimulations = TestAttempt::with(['user', 'patient'])
            ->latest()
            ->limit(8)
            ->get();

        $topTeachers = User::where('role_id', Role::TEACHER_ID)
            ->withCount('patients')
            ->orderByDesc('patients_count')
            ->limit(5)
            ->get();

        $topPatients = Patient::withCount('testAttempts')
            ->orderByDesc('test_attempts_count')
            ->limit(5)
            ->get();

        return view('pages.admin.dashboard', compact(
            'totalTeachers',
            'totalStudents',
            'totalSubjects',
            'totalPatients',
            'totalSimulations',
            'pendingGrading',
            'recentSimulations',
            'topTeachers',
            'topPatients'
        ));
    }
}
