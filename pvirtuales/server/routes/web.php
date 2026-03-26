<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegistrationController;
use App\Http\Controllers\PatientController;
use App\Http\Controllers\Teacher\TeacherDashboardController;
use App\Http\Controllers\Consultation\ConsultationController;
use App\Http\Controllers\Teacher\TeacherFollowupController;
use App\Http\Controllers\Student\StudentDashboardController;
use App\Http\Controllers\Simulation\SimulationController;
use App\Http\Controllers\QuestionController;
use App\Http\Controllers\Teacher\SubjectController;

/*
|--------------------------------------------------------------------------
| Web Routes — SimulAI
|--------------------------------------------------------------------------
|
| ESTADO ACTUAL DE CONSTRUCCIÓN:
|
|   ✅ ACTIVAS    → Tienen controlador + vista funcionando
|   💬 COMENTADAS → Pendientes de construir (no dan error 404/500)
|
| ESTRUCTURA FINAL PREVISTA:
|   1. Pública         → Home (redirige por rol si autenticado)
|   2. Guest           → Login y registro por invitación
|   3. Auth            → Logout
|   4. Admin           → Gestión global (middleware: role.admin) [PENDIENTE]
|   5. Profesor        → Asignaturas, pacientes, seguimiento (middleware: role.teacher)
|   6. Alumno          → Asignaturas, simulaciones (middleware: role.student) [PENDIENTE]
|   7. Compartidas     → Simulación y chat
|
*/

/* ====================================================================
   1. PÚBLICA
   Redirige al dashboard del rol si ya está autenticado.
   ==================================================================== */

Route::get('/', function () {
    if (!auth()->check()) {
        return view('pages.index');
    }

    return match (true) {
        auth()->user()->isAdmin() => redirect()->route('teacher.dashboard'), // admin usa teacher por ahora
        auth()->user()->isTeacher() => redirect()->route('teacher.dashboard'),
        default => redirect()->route('student.dashboard'), // alumno pendiente
    };
})->name('home');

/* ====================================================================
   2. GUEST — Solo accesible si NO está autenticado
   ==================================================================== */

Route::middleware(['guest'])->group(function () {

    // Login
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login'])->name('login.submit');

    // Registro por invitación 
    Route::get('/registro/{token}', [RegistrationController::class, 'showRegistrationForm'])->name('register.form');
    Route::post('/registro/crear', [RegistrationController::class, 'createAccount'])->name('register.create');
});

/* ====================================================================
   3. AUTH — Logout
   ==================================================================== */

Route::middleware(['auth'])->group(function () {
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');
});

/* ====================================================================
   4. ADMIN — [PENDIENTE DE CONSTRUIR]
   Middleware: auth + role.admin
   ==================================================================== */

// Route::middleware(['auth', 'role.admin'])
//     ->prefix('admin')
//     ->name('admin.')
//     ->group(function () {
//         Route::get('/dashboard', fn() => view('pages.admin.dashboard'))->name('dashboard');
//         Route::get('/usuarios', fn() => view('pages.admin.users.index'))->name('users.index');
//         Route::get('/asignaturas', fn() => view('pages.admin.subjects.index'))->name('subjects.index');
//         Route::get('/pacientes', fn() => view('pages.admin.patients.index'))->name('patients.index');
//         Route::get('/consultas', fn() => view('pages.admin.consultations.index'))->name('consultations.index');
//     });

/* ====================================================================
   5. PROFESOR
   Middleware: auth + role.teacher
   ==================================================================== */

Route::middleware(['auth', 'role.teacher'])
    ->prefix('profesor')
    ->name('teacher.')
    ->group(function () {

        // ✅ Dashboard del profesor
        Route::get('/dashboard', [TeacherDashboardController::class, 'index'])->name('dashboard');

        Route::prefix('asignaturas')->name('subjects.')->group(function () {
            Route::get('/', [SubjectController::class, 'index'])->name('index');
            Route::get('/crear', [SubjectController::class, 'create'])->name('create');
            Route::post('/', [SubjectController::class, 'store'])->name('store');
            Route::get('/{subject}', [SubjectController::class, 'show'])->name('show');
            Route::get('/{subject}/editar', [SubjectController::class, 'edit'])->name('edit');
            Route::put('/{subject}', [SubjectController::class, 'update'])->name('update');
            Route::delete('/{subject}', [SubjectController::class, 'destroy'])->name('destroy');
            Route::post('/{subject}/alumnos', [SubjectController::class, 'enrollStudent'])->name('students.enroll');
            Route::delete('/{subject}/alumnos/{user}', [SubjectController::class, 'unenrollStudent'])->name('students.unenroll');
            Route::post('/{subject}/colaboradores', [SubjectController::class, 'inviteCollaborator'])->name('collaborators.invite');
            Route::delete('/{subject}/colaboradores/{user}', [SubjectController::class, 'removeCollaborator'])->name('collaborators.remove');
        });

        // --- Pacientes del profesor ---
        Route::prefix('pacientes')->name('patients.')->group(function () {

            // ✅ Listado
            Route::get('/', [PatientController::class, 'index'])->name('index');

            // ✅ Selección de modo básico / avanzado
            Route::get('/crear', [PatientController::class, 'selectMode'])->name('create');

            // ✅ Formulario básico
            Route::get('/crear/basico', [PatientController::class, 'createBasic'])->name('create.basic');

            // ✅ Formulario avanzado
            Route::get('/crear/avanzado', [PatientController::class, 'createAdvanced'])->name('create.advanced');

            // ✅ Guardar (ambos modos)
            Route::post('/crear', [PatientController::class, 'store'])->name('store');

            // ✅ Previsualizar prompt
            Route::get('/{patient}/previsualizar', [PatientController::class, 'preview'])->name('preview');

            // ✅ Publicar
            Route::post('/{patient}/publicar', [PatientController::class, 'publish'])->name('publish');

            // ✅ Eliminar
            Route::delete('/{patient}/{origen}', [PatientController::class, 'destroy'])->name('destroy');

            // ✅ Editar paciente
            Route::get('/{patient}/editar', [PatientController::class, 'edit'])->name('edit');
            Route::put('/{patient}', [PatientController::class, 'update'])->name('update');



            // ✅ Test de evaluación
    
            Route::put('/{patient}/test/config', [QuestionController::class, 'updateConfig'])->name('test.config');

            Route::get('/{patient}/test', [QuestionController::class, 'manage'])->name('test');
            Route::post('/{patient}/test', [QuestionController::class, 'store'])->name('test.store');
            Route::delete('/{patient}/test/{question}', [QuestionController::class, 'destroy'])->name('test.destroy');
            Route::put('/{patient}/test/{question}', [QuestionController::class, 'update'])->name('test.update');

        });

        // --- Seguimiento ---
        Route::prefix('consultas')->name('consultations.')->group(function () {
            Route::get('/', [TeacherFollowupController::class, 'consultations'])->name('index');
        });

        Route::prefix('resultados')->name('results.')->group(function () {
            Route::get('/', [TeacherFollowupController::class, 'results'])->name('index');
            Route::get('/{attempt}', [TeacherFollowupController::class, 'showResult'])->name('show');
            Route::post('/{attempt}/calificar', [TeacherFollowupController::class, 'grade'])->name('grade');
        });

    });

/* ====================================================================
   6. ALUMNO — [PENDIENTE DE CONSTRUIR]
   Middleware: auth + role.student
   ==================================================================== */

Route::middleware(['auth', 'role.student'])
    ->prefix('alumno')
    ->name('student.')
    ->group(function () {
        Route::get('/dashboard', [StudentDashboardController::class, 'index'])->name('dashboard');
        Route::get('/pacientes', [StudentDashboardController::class, 'patients'])->name('patients.index');

        // Envío del cuestionario por parte del alumno
        Route::post('/test/{patient}/submit', [QuestionController::class, 'submit'])->name('patients.test.submit');

        Route::get('/asignaturas', [StudentDashboardController::class, 'subjects'])->name('subjects.index');
        Route::get('/consultas', [StudentDashboardController::class, 'consultations'])->name('consultations.index');
        Route::get('/resultados', [StudentDashboardController::class, 'results'])->name('results.index');


    });

/* ====================================================================
   7. COMPARTIDAS — Simulación y chat (auth requerido)
   ==================================================================== */

Route::middleware(['auth'])->group(function () {

    // ✅ Simulación (chat con el paciente virtual)
    Route::get('/simulacion/{aiModel}/{patientId}', [SimulationController::class, 'start'])->name('simulation.start');
    Route::post('/simulacion/enviar', [SimulationController::class, 'sendMessage'])->name('simulation.send');

    // ✅ Test del alumno tras la simulación
    Route::get('/test/{patient}', [QuestionController::class, 'take'])->name('patients.test.take');
});