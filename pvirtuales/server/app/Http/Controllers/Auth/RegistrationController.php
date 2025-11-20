<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use App\Models\UserInvitation;
use App\Models\User;
use App\Mail\SendRegistrationLink;
use Carbon\Carbon; // Para gestionar la caducidad

class RegistrationController extends Controller
{
    /**
     * Muestra la vista de pre-registro (campo de email).
     */
    public function showPreRegisterForm()
    {
        return view('pages.auth.pre-register');
    }

    /**
     * Envía el enlace de invitación.
     */
    public function sendInviteLink(Request $request)
    {
        $request->validate([
            'email' => 'required|email|unique:users,email'
        ], [
            'email.unique' => 'Este email ya está registrado. Por favor, inicia sesión.'
        ]);

        $email = $request->input('email');

        // 2. Si ya existe una invitación, la borramos para crear una nueva
        UserInvitation::where('email', $email)->delete();

        // 3. Generar un token seguro
        $token = Str::random(60);

        // 4. Guardar la invitación en la BBDD
        $invite = UserInvitation::create([
            'email' => $email,
            'token' => $token
        ]);

        // 5. Enviar el email usando nuestro "Mailable"
        Mail::to($email)->send(new SendRegistrationLink($invite->token));

        // 6. Redirigir de vuelta con mensaje de éxito
        return redirect()->back()->with('success', '¡Genial! Te hemos enviado un enlace a tu correo. Revísalo para continuar.');
    }

    /**
     * Muestra el formulario de registro final.
     */
    public function showRegistrationForm(string $token)
    {
        // 1. Buscar la invitación por el token
        $invite = UserInvitation::where('token', $token)->first();

        // 2. Comprobar si es válida o ha caducado (ej. 24 horas)
        if (!$invite || $invite->created_at < Carbon::now()->subHours(24)) {
            return view('pages.auth.invalid-token');
        }

        // 3. Si es válida, mostramos el formulario
        return view('pages.auth.register', [
            'email' => $invite->email,
            'token' => $invite->token
        ]);
    }

    /**
     * Crea la cuenta de usuario final.
     */
    public function createAccount(Request $request)
    {
        $request->validate([
            'token' => 'required|string',
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:150',
            'password' => 'required|string|min:8|confirmed', // 'confirmed' busca 'password_confirmation'
        ]);

        // 2. Volver a verificar el token
        $invite = UserInvitation::where('token', $request->token)->first();

        if (!$invite) {
            return view('pages.auth.invalid-token');
        }

        // 3. Crear el usuario en la tabla 'users'
        $user = User::create([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'email' => $invite->email, // ¡Obtenemos el email de la BBDD, no del formulario!
            'password' => Hash::make($request->password), // Hashear la contraseña
            'birth_date' => $request->birth_date,
            'gender' => $request->gender,
            'role_id' => Role::STUDENT_ID, // Por defecto, rol 1 = STUDENT
        ]);

        // 4. Borrar la invitación (para que el enlace no se use de nuevo)
        $invite->delete();

        // 5. Iniciar sesión al nuevo usuario
        Auth::login($user);

        // 6. Redirigir al dashboard o página principal
        return redirect('/');
    }
}

