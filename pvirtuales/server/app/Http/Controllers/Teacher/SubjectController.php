<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Subject;
use App\Models\User;
use App\Models\UserInvitation;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;
use App\Mail\StudentEnrolled;
use App\Mail\StudentInvited;
use App\Mail\CollaboratorInvited;
use App\Mail\CollaboratorInvitedNew;
use Illuminate\Support\Facades\DB;  


/**
 * Controlador para la gestión de asignaturas del profesor.
 * 
 * El propietario puede crear, editar, eliminar y gestionar miembros.
 * Los colaboradores pueden crear pacientes pero no editar ni eliminar la asignatura.
 */
class SubjectController extends Controller
{
    /**
     * Listado de asignaturas donde el profesor es propietario o colaborador.
     */
    public function index()
    {
        $userId = Auth::id();

        // Asignaturas propias
        $ownedSubjects = Subject::with(['students', 'collaborators', 'patients'])
            ->where('created_by_user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->get();

        // Asignaturas donde es colaborador
        $collaboratingSubjects = Auth::user()->collaboratingSubjects()
            ->with(['students', 'patients', 'creator'])
            ->orderBy('created_at', 'desc')
            ->get();

        return view('pages.teacher.subjects.index', compact(
            'ownedSubjects',
            'collaboratingSubjects'
        ));
    }

    /**
     * Formulario de creación de asignatura.
     */
    public function create()
    {
        return view('pages.teacher.subjects.create');
    }

    /**
     * Guardar nueva asignatura.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'code' => 'required|string',
            'institution' => 'required|string',
        ]);

        $subject = Subject::create([
            ...$validated,
            'created_by_user_id' => Auth::id(),
        ]);

        return redirect()->route('teacher.subjects.show', $subject)
            ->with('success', 'Asignatura creada correctamente.');
    }

    /**
     * Detalle de la asignatura con pacientes, alumnos y colaboradores.
     */
    public function show(Subject $subject)
    {
        $this->authorizeAccess($subject);

        $subject->load(['students', 'collaborators', 'patients']);

        return view('pages.teacher.subjects.show', compact('subject'));
    }

    /**
     * Formulario de edición. Solo el propietario puede acceder.
     */
    public function edit(Subject $subject)
    {
        $this->authorizeOwner($subject);

        return view('pages.teacher.subjects.edit', compact('subject'));
    }

    /**
     * Guardar cambios de la asignatura. Solo el propietario puede hacerlo.
     */
    public function update(Request $request, Subject $subject)
    {
        $this->authorizeOwner($subject);

        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'code' => 'required|string',
            'institution' => 'required|string',
        ]);

        $subject->update($validated);

        return redirect()->route('teacher.subjects.show', $subject)
            ->with('success', 'Asignatura actualizada correctamente.');
    }

    /**
     * Eliminar asignatura. Solo el propietario puede hacerlo.
     * La confirmación se gestiona en la vista.
     */
    public function destroy(Subject $subject)
    {
        $this->authorizeOwner($subject);

        $subject->delete();

        return redirect()->route('teacher.subjects.index')
            ->with('success', 'Asignatura eliminada correctamente.');
    }

    /**
     * Inscribir alumno en la asignatura por email.
     * Si existe en la BD → inscribir y notificar.
     * Si no existe → crear invitación y enviar email de registro.
     */
    public function enrollStudent(Request $request, Subject $subject)
    {
        $this->authorizeAccess($subject);

        $request->validate(['email' => 'required|email']);

        try {
            $result = $this->processStudentEmail($request->input('email'), $subject, Auth::user());
        } catch (\Exception $e) {
            return back()->with('error', 'No se ha podido enviar el email. Vuelve a intentarlo.');
        }

        return match ($result) {
            'enrolled' => back()->with('success', 'Alumno inscrito correctamente. Se le ha notificado por email.'),
            'invited' => back()->with('success', 'Invitación enviada correctamente al alumno.'),
            'already_enrolled' => back()->with('error', 'Este alumno ya está inscrito en la asignatura.'),
        };
    }


    /**
     * Desinscribir alumno de la asignatura.
     */
    public function unenrollStudent(Subject $subject, User $user)
    {
        $this->authorizeAccess($subject);

        $subject->students()->detach($user->id);

        return back()->with('success', 'Alumno eliminado de la asignatura.');
    }

    /**
     * Invitar a un profesor colaborador por email.
     */
    public function inviteCollaborator(Request $request, Subject $subject)
    {
        $this->authorizeOwner($subject);

        $request->validate([
            'email' => 'required|email',
        ]);

        $email = $request->input('email');
        $teacher = Auth::user();

        $user = User::where('email', $email)
            ->where('role_id', Role::TEACHER_ID)
            ->first();

        if ($user) {
            if ($subject->collaborators()->where('user_id', $user->id)->exists()) {
                return back()->with('error', 'Este profesor ya es colaborador de la asignatura.');
            }

            // No puede invitarse a sí mismo
            if ($user->id === $teacher->id) {
                return back()->with('error', 'No puedes invitarte a ti mismo como colaborador.');
            }

            $subject->collaborators()->attach($user->id, ['role' => 'collaborator']);

            Mail::to($user->email)->send(new CollaboratorInvited($user, $teacher, $subject));

            return back()->with('success', 'Colaborador añadido correctamente. Se le ha notificado por email.');
        }

        // El profesor no existe → crear invitación y enviar email de registro
        $token = Str::random(64);

        UserInvitation::create([
            'email' => $email,
            'token' => $token,
            'role_id' => Role::TEACHER_ID,
            'invited_by_user_id' => $teacher->id,
            'subject_id' => $subject->id,
        ]);

        Mail::to($email)->send(new CollaboratorInvitedNew($teacher, $subject, $token));

        return back()->with('success', 'Invitación enviada correctamente al profesor.');
    }

    /**
     * Eliminar colaborador de la asignatura.
     */
    public function removeCollaborator(Subject $subject, User $user)
    {
        $this->authorizeOwner($subject);

        $subject->collaborators()->detach($user->id);

        return back()->with('success', 'Colaborador eliminado de la asignatura.');
    }

    /* ===========================
     * MÉTODOS PRIVADOS DE AUTORIZACIÓN
     * =========================== */

    /**
     * Verifica que el usuario autenticado es propietario O colaborador.
     * Si no, aborta con 403.
     */
    private function authorizeAccess(Subject $subject): void
    {
        $userId = Auth::id();

        $isOwner = $subject->created_by_user_id === $userId;
        $isCollaborator = $subject->collaborators()->where('user_id', $userId)->exists();

        if (!$isOwner && !$isCollaborator) {
            abort(403);
        }
    }

    /**
     * Verifica que el usuario autenticado es el propietario.
     * Si no, aborta con 403.
     */
    private function authorizeOwner(Subject $subject): void
    {
        if ($subject->created_by_user_id !== Auth::id()) {
            abort(403);
        }
    }

    private function processStudentEmail(string $email, Subject $subject, User $teacher): string
    {
        $user = User::where('email', $email)->where('role_id', Role::STUDENT_ID)->first();

        if ($user) {
            if ($subject->students()->where('user_id', $user->id)->exists()) {
                return 'already_enrolled';
            }
            $subject->students()->attach($user->id, ['role' => 'student']);
            Mail::to($user->email)->send(new StudentEnrolled($user, $teacher, $subject));
            return 'enrolled';
        }

        $token = Str::random(64);

        DB::table('user_invitations')->insert([
            'email' => $email,
            'token' => $token,
            'role_id' => Role::STUDENT_ID,
            'invited_by_user_id' => $teacher->id,
            'subject_id' => $subject->id,
            'created_at' => now(),
        ]);

        Mail::to($email)->send(new StudentInvited($teacher, $subject, $token));
        return 'invited';
    }


    public function bulkEnrollStudents(Request $request, Subject $subject)
    {
        $this->authorizeAccess($subject);


        // Validación — solo CSV
        $request->validate(['file' => 'required|file|mimes:csv,txt|max:2048']);

        $teacher = Auth::user();
        $file = $request->file('file');
        // Llamada sin extensión
        $emails = $this->extractEmailsFromFile($file->getRealPath());

        if (empty($emails)) {
            return back()->with('bulk_error', 'No se encontraron emails válidos en el archivo.');
        }

        $enrolled = [];
        $invited = [];
        $alreadyEnrolled = [];
        $failed = [];

        foreach ($emails as $email) {
            try {
                $result = $this->processStudentEmail($email, $subject, $teacher);
                match ($result) {
                    'enrolled' => $enrolled[] = $email,
                    'invited' => $invited[] = $email,
                    'already_enrolled' => $alreadyEnrolled[] = $email,
                };
            } catch (\Exception $e) {
                $failed[] = $email;
            }
        }

        return back()->with([
            'bulk_enrolled' => $enrolled,
            'bulk_invited' => $invited,
            'bulk_already_enrolled' => $alreadyEnrolled,
            'bulk_failed' => $failed,
        ]);
    }

    private function extractEmailsFromFile(string $path): array
    {
        $emails = [];
        $firstLine = file($path)[0] ?? '';
        $separator = substr_count($firstLine, ';') >= substr_count($firstLine, ',') ? ';' : ',';

        if (($handle = fopen($path, 'r')) !== false) {
            while (($row = fgetcsv($handle, 1000, $separator)) !== false) {
                foreach ($row as $cell) {
                    $cell = trim($cell);
                    if (filter_var($cell, FILTER_VALIDATE_EMAIL)) {
                        $emails[] = strtolower($cell);
                    }
                }
            }
            fclose($handle);
        }

        return array_unique($emails);
    }



}