<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegistrationController;
use App\Http\Controllers\PatientController;
use App\Http\Controllers\Teacher\TeacherDashboardController;
use App\Http\Controllers\Consultation\ConsultationController;
use App\Http\Controllers\Simulation\SimulationController;
use App\Http\Controllers\QuestionController;

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
        default => redirect()->route('teacher.dashboard'), // alumno pendiente
    };
})->name('home');

/* ====================================================================
   2. GUEST — Solo accesible si NO está autenticado
   ==================================================================== */

Route::middleware(['guest'])->group(function () {

    // Login
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login'])->name('login.submit');

    // Registro por invitación (flujo de dos pasos)
    Route::get('/registro', [RegistrationController::class, 'showPreRegisterForm'])->name('register.start');
    Route::post('/registro', [RegistrationController::class, 'sendInviteLink'])->name('register.sendlink');
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

        // --- Asignaturas [PENDIENTE] ---
        // Route::prefix('asignaturas')->name('subjects.')->group(function () {
        //     Route::get('/', fn() => view('pages.teacher.subjects.index'))->name('index');
        //     Route::get('/crear', fn() => view('pages.teacher.subjects.create'))->name('create');
        //     Route::post('/', fn() => null)->name('store');
        //     Route::get('/{subject}', fn() => view('pages.teacher.subjects.show'))->name('show');
        //     Route::get('/{subject}/editar', fn() => view('pages.teacher.subjects.edit'))->name('edit');
        //     Route::put('/{subject}', fn() => null)->name('update');
        //     Route::delete('/{subject}', fn() => null)->name('destroy');
        //     Route::post('/{subject}/alumnos', fn() => null)->name('students.enroll');
        //     Route::delete('/{subject}/alumnos/{user}', fn() => null)->name('students.unenroll');
        // });
    
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
            Route::delete('/{patient}', [PatientController::class, 'destroy'])->name('destroy');

            // ✅ Test de evaluación
            Route::get('/{patient}/test', [QuestionController::class, 'manage'])->name('test');
            Route::post('/{patient}/test', [QuestionController::class, 'store'])->name('test.store');
            Route::delete('/{patient}/test/{question}', [QuestionController::class, 'destroy'])->name('test.destroy');
        });

        // --- Seguimiento [PENDIENTE] ---
        // Route::prefix('consultas')->name('consultations.')->group(function () {
        //     Route::get('/', fn() => view('pages.teacher.consultations.index'))->name('index');
        //     Route::get('/{attempt}', fn() => view('pages.teacher.consultations.show'))->name('show');
        // });
    
        // Route::prefix('resultados')->name('results.')->group(function () {
        //     Route::get('/', fn() => view('pages.teacher.results.index'))->name('index');
        //     Route::get('/{attempt}', fn() => view('pages.teacher.results.show'))->name('show');
        // });
    });

/* ====================================================================
   6. ALUMNO — [PENDIENTE DE CONSTRUIR]
   Middleware: auth + role.student
   ==================================================================== */

// Route::middleware(['auth', 'role.student'])
//     ->prefix('alumno')
//     ->name('student.')
//     ->group(function () {
//         Route::get('/dashboard', fn() => view('pages.student.dashboard'))->name('dashboard');
//         Route::prefix('asignaturas')->name('subjects.')->group(function () {
//             Route::get('/', fn() => view('pages.student.subjects.index'))->name('index');
//             Route::get('/{subject}', fn() => view('pages.student.subjects.show'))->name('show');
//         });
//         Route::prefix('consultas')->name('consultations.')->group(function () {
//             Route::get('/', fn() => view('pages.student.consultations.index'))->name('index');
//             Route::get('/{attempt}', fn() => view('pages.student.consultations.show'))->name('show');
//         });
//         Route::prefix('resultados')->name('results.')->group(function () {
//             Route::get('/', fn() => view('pages.student.results.index'))->name('index');
//         });
//     });

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