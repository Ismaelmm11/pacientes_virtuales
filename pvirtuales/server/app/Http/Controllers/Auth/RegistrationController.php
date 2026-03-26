<?php

//pvirtuales/server/app/Http/Controllers/Auth/RegistrationController.php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\CreateAccountRequest;
use App\Models\Role;
use App\Models\User;
use App\Models\UserInvitation;
use App\Mail\SendRegistrationLink;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

/**
 * Controlador de registro basado en invitaciones.
 * 
 * Implementa un flujo de registro en 4 pasos:
 * 
 * Paso 1 → showPreRegisterForm()   → Muestra formulario para introducir email
 * Paso 2 → sendInviteLink()        → Valida email y envía enlace de invitación
 * Paso 3 → showRegistrationForm()  → Muestra formulario de registro completo (llega desde el email)
 * Paso 4 → createAccount()         → Valida datos, crea la cuenta y loguea al usuario
 */
class RegistrationController extends Controller
{
    // ==================== PASO 1: PRE-REGISTRO ====================

    /**
     * Muestra el formulario de pre-registro donde el usuario introduce su email.
     *
     * @return \Illuminate\View\View
     */
    public function showPreRegisterForm()
    {
        return view('pages.auth.pre-register');
    }

    // ==================== PASO 2: ENVÍO DE INVITACIÓN ====================

    /**
     * Genera un token único y envía el enlace de invitación por correo.
     * Si ya existía una invitación previa para ese email, la reemplaza.
     *
     * @param SendInvitateLinkRequest $request Petición validada con el campo 'email'
     * @return \Illuminate\Http\RedirectResponse
     */
    public function sendInviteLink(SendInvitateLinkRequest $request)
    {
        $email = $request->input('email');

        // Si ya existe una invitación pendiente, la borramos para crear una nueva
        UserInvitation::where('email', $email)->delete();

        // Generar token seguro y guardar la invitación
        $invite = UserInvitation::create([
            'email' => $email,
            'token' => Str::random(60),
        ]);

        // Enviar el email con el enlace de registro
        Mail::to($email)->send(new SendRegistrationLink($invite->token));

        return redirect()->back()->with('success', '¡Genial! Te hemos enviado un enlace a tu correo. Revísalo para continuar.');
    }

    // ==================== PASO 3: FORMULARIO DE REGISTRO ====================

    /**
     * Muestra el formulario de registro completo si el token es válido y no ha caducado.
     * Los tokens caducan a las 24 horas de su creación.
     *
     * @param string $token Token único recibido por email
     * @return \Illuminate\View\View
     */
    public function showRegistrationForm(string $token)
    {
        $invite = UserInvitation::where('token', $token)->first();

        // Comprobar si el token existe y no ha caducado (24 horas)
        if (!$invite || $invite->created_at < Carbon::now()->subHours(24)) {
            return view('pages.auth.invalid-token');
        }

        return view('pages.auth.register', [
            'email' => $invite->email,
            'token' => $invite->token,
        ]);
    }

    // ==================== PASO 4: CREACIÓN DE CUENTA ====================

    /**
     * Crea la cuenta de usuario, borra la invitación e inicia sesión automáticamente.
     * Se ejecuta dentro de una transacción para garantizar la integridad de los datos.
     *
     * @param CreateAccountRequest $request Petición validada con todos los campos del formulario
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\View\View
     */
    public function createAccount(CreateAccountRequest $request)
    {
        // Verificar que el token sigue siendo válido
        $invite = UserInvitation::where('token', $request->token)->first();

        if (!$invite) {
            return view('pages.auth.invalid-token');
        }

        return DB::transaction(function () use ($request, $invite) {
            // Crear el usuario (el email se obtiene de la invitación, no del formulario, por seguridad)
            $user = User::create([
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'email' => $invite->email,
                'password' => Hash::make($request->password),
                'birth_date' => $request->birth_date,
                'gender' => $request->gender,
                'role_id' => Role::STUDENT_ID,
            ]);

            // Borrar la invitación para que el enlace no se pueda reutilizar
            $invite->delete();

            // Iniciar sesión automáticamente
            Auth::login($user);

            return redirect('/');
        });
    }
}