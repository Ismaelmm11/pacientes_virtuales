<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;


/**
 * Gestiona el proceso de autenticación de usuarios existentes.
 * * Este controlador se encarga de mostrar el formulario de acceso,
 * validar las credenciales mediante Auth::attempt y gestionar 
 * el cierre seguro de sesiones regenerando tokens.
 */
class LoginController extends Controller
{
    /**
     * Muestra el formulario de login.
     * * @return \Illuminate\View\View Vista del formulario de acceso.
     */
    public function showLoginForm()
    {
        return view('pages.auth.login');
    }

    /**
     * Procesa el intento de autenticación.
     * * @param \Illuminate\Http\Request $request Petición con los campos 'email' y 'password'.
     * @return \Illuminate\Http\RedirectResponse Redirección al dashboard o vuelta al formulario con errores.
     */
    public function login(Request $request)
    {
        // 1. Validar los datos
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        // 2. Intentar loguear al usuario
        // (Auth::attempt cifra la contraseña automáticamente y la compara)
        if (Auth::attempt($credentials, $request->filled('remember'))) {
            
            // 3. Si es correcto: Regenerar la sesión por seguridad (evita Session Fixation)
            $request->session()->regenerate();

            // 4. Redirigir al usuario a donde quería ir (o al inicio)
            return redirect()->intended('/');
        }

        // 5. Si falla: Volver atrás con un error
        return back()->withErrors([
            'email' => 'Las credenciales proporcionadas no coinciden con nuestros registros.',
        ])->onlyInput('email');
    }

    /**
     * Cierra la sesión del usuario de forma segura.
     * * @param \Illuminate\Http\Request $request Petición actual para cerrar la sesión.
     * @return \Illuminate\Http\RedirectResponse Redirección al inicio después de cerrar sesión.
     */
    public function logout(Request $request)
    {
        Auth::logout();

        // Invalidar la sesión y regenerar el token CSRF (limpieza de seguridad)
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}