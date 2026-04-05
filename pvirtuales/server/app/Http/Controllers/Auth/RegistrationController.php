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
            $user = User::create([
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'email' => $invite->email,
                'password' => Hash::make($request->password),
                'birth_date' => $request->birth_date,
                'gender' => $request->gender,
                'role_id' => $invite->role_id ?? Role::STUDENT_ID,
            ]);

            if ($invite->subject_id) {
                $role = ($invite->role_id === Role::STUDENT_ID) ? 'student' : 'collaborator';
                DB::table('subject_user')->insert([
                    'subject_id' => $invite->subject_id,
                    'user_id' => $user->id,
                    'role' => $role,
                ]);
            }

            $invite->delete();
            Auth::login($user);
            return redirect('/');
        });
    }
}