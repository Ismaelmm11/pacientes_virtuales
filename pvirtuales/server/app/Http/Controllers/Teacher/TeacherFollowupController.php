<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Answer;
use App\Models\Patient;
use App\Models\TestAttempt;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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

        $totalCount     = TestAttempt::whereIn('patient_id', $patientIds)->count();
        $pendingCount   = TestAttempt::whereIn('patient_id', $patientIds)->whereNull('submitted_at')->whereNotNull('interview_transcript')->count();
        $gradingCount   = TestAttempt::whereIn('patient_id', $patientIds)->whereNotNull('submitted_at')->whereNull('final_score')->count();
        $completedCount = TestAttempt::whereIn('patient_id', $patientIds)->whereNotNull('final_score')->count();

        return view('pages.teacher.consultations.index', compact(
            'attempts', 'totalCount', 'pendingCount', 'gradingCount', 'completedCount'
        ));
    }

    public function results()
    {
        $patientIds = $this->teacherPatientIds();

        $attempts = TestAttempt::with(['patient.subject', 'user'])
            ->whereIn('patient_id', $patientIds)
            ->whereNotNull('submitted_at')
            ->orderBy('submitted_at', 'desc')
            ->paginate(20);

        $totalSubmitted = TestAttempt::whereIn('patient_id', $patientIds)->whereNotNull('submitted_at')->count();
        $pendingGrading = TestAttempt::whereIn('patient_id', $patientIds)->whereNotNull('submitted_at')->whereNull('final_score')->count();
        $gradedCount    = TestAttempt::whereIn('patient_id', $patientIds)->whereNotNull('final_score')->count();
        $avgGrade       = TestAttempt::whereIn('patient_id', $patientIds)->whereNotNull('final_score')->avg('final_score');

        return view('pages.teacher.results.index', compact(
            'attempts', 'totalSubmitted', 'pendingGrading', 'gradedCount', 'avgGrade'
        ));
    }

    public function showResult(TestAttempt $attempt)
    {
        $this->authorizeAttempt($attempt);
        $attempt->load(['patient.subject', 'user', 'answers.question']);

        return view('pages.teacher.results.show', compact('attempt'));
    }

    public function grade(Request $request, TestAttempt $attempt)
    {
        $this->authorizeAttempt($attempt);

        $scores = $request->input('scores', []); // [answer_id => score]

        foreach ($scores as $answerId => $score) {
            $answer = Answer::find($answerId);
            if (!$answer || $answer->test_attempt_id !== $attempt->id) continue;

            $maxPoints = (float) ($answer->question->points ?? 10);
            $score = max(0, min((float) $score, $maxPoints));
            $answer->update([
                'score'      => $score,
                'is_correct' => $score > 0,
            ]);
        }

        $finalScore = $attempt->answers()->sum('score');
        $attempt->update(['final_score' => round($finalScore, 2)]);

        return redirect()
            ->route('teacher.results.show', $attempt)
            ->with('success', 'Corrección guardada. Nota final: ' . number_format($finalScore, 2) . ' / 10.');
    }

    private function authorizeAttempt(TestAttempt $attempt): void
    {
        if (!in_array($attempt->patient_id, $this->teacherPatientIds())) {
            abort(403);
        }
    }
}
