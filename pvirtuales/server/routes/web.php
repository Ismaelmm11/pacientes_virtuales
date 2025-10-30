<?php

use Illuminate\Support\Facades\Route;


use App\Models\User;

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
