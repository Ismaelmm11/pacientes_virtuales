<?php

use Illuminate\Support\Facades\Route;
use App\Models\User;
// Importamos todos los controladores necesarios
use App\Http\Controllers\Auth\RegistrationController;
use App\Http\Controllers\Auth\LoginController;       // <-- Nuevo
use App\Http\Controllers\SimulationController;       // <-- Nuevo

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Ruta de prueba de base de datos (opcional, la puedes dejar)
Route::get('/test-db', function () {
    $user = User::find(1);
    if ($user) {
        return '¡Conexión exitosa! El usuario es ' . $user->first_name . ' y su rol es: ' . $user->role->name;
    }
});

// Página de Inicio (Dashboard)
Route::get('/', function () {
    return view('pages.index');
});


// --- RUTAS DE LOGIN Y LOGOUT (NUEVAS) ---
// Necesarias para que funcione el @auth del index.blade.php
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login'])->name('login.submit');
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');


// --- RUTAS DE REGISTRO (YA LAS TENÍAS) ---
Route::get('/iniciar-registro', [RegistrationController::class, 'showPreRegisterForm'])->name('register.start');
Route::post('/iniciar-registro', [RegistrationController::class, 'sendInviteLink'])->name('register.sendlink');
Route::get('/registrar/{token}', [RegistrationController::class, 'showRegistrationForm'])->name('register.form');
Route::post('/registrar', [RegistrationController::class, 'createAccount'])->name('register.create');


// --- RUTAS DE SIMULACIÓN (NUEVAS) ---
// Estas rutas están protegidas: solo funcionan si el usuario ha iniciado sesión
Route::middleware(['auth'])->group(function () {
    
    // 1. Iniciar el chat (Carga la vista del chat con el historial inicial)
    // Captura qué IA ({ai}) y qué paciente ({patient}) ha elegido el usuario
    Route::get('/simulacion/{ai}/{patient}', [SimulationController::class, 'start'])
         ->name('simulation.start');

    // 2. Enviar mensaje (Recibe la petición AJAX del Javascript del chat)
    Route::post('/simulacion/enviar', [SimulationController::class, 'sendMessage'])
         ->name('simulation.send');
         
});