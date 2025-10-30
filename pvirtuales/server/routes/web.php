<?php

use Illuminate\Support\Facades\Route;


use App\Models\User;
use App\Http\Controllers\Auth\RegistrationController;

Route::get('/test-db', function () {
    
    // Busca al usuario con ID 1 (Carlos García, el profesor)
    $user = User::find(1);

    if ($user) {
        // Gracias a la función role() que hemos creado:
        // 1. $user->role   -> Accede a la tabla 'roles'
        // 2. ->name         -> Obtiene la columna 'name' de esa tabla
        return '¡Conexión exitosa! El usuario es ' . $user->first_name . ' y su rol es: ' . $user->role->name;
    }
    
});

Route::get('/', function () {
    return view('pages.index');
});


// --- RUTAS DEL NUEVO FLUJO DE REGISTRO ---

// 1. Mostrar la página para introducir el email
Route::get('/iniciar-registro', [RegistrationController::class, 'showPreRegisterForm'])->name('register.start');

// 2. Procesar el envío del email y mandar el enlace
Route::post('/iniciar-registro', [RegistrationController::class, 'sendInviteLink'])->name('register.sendlink');

// 3. Mostrar el formulario de registro final (desde el enlace del email)
Route::get('/registrar/{token}', [RegistrationController::class, 'showRegistrationForm'])->name('register.form');

// 4. Crear la cuenta de usuario
Route::post('/registrar', [RegistrationController::class, 'createAccount'])->name('register.create');
