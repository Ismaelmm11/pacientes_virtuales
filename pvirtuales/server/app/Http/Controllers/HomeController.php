<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;

/**
 * Gestiona la página principal de la aplicación.
 * 
 * Muestra una vista diferente según el estado de autenticación:
 * - Invitado: Página de bienvenida con opciones de login/registro.
 * - Autenticado: Panel con accesos a creación de pacientes y consultas.
 */
class HomeController extends Controller
{
    /**
     * Muestra la página de inicio.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        return view('pages.index');
    }
}