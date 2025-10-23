<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Aquí es donde registras las rutas de la API.
|
*/

// NUEVA RUTA DE PRUEBA:
// Cuando se acceda a /api/hello, devolverá un JSON
Route::get('/hello', function () {
    return response()->json([
        'message' => 'Hola Mundo'
    ]);
});