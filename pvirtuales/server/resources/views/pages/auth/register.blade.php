{{--
|--------------------------------------------------------------------------
| Página de Registro Completo (Paso 3)
|--------------------------------------------------------------------------
|
| Formulario final de captación de datos. 
| Solo accesible si el usuario llega con un token válido desde su email.
|
--}}

<x-layouts.app title="Completar Registro">
    <x-slot:styles>
        <link href="{{ asset('css/auth.css') }}" rel="stylesheet">
    </x-slot:styles>

    <div class="auth-wrapper">
        <div class="auth-card">
            <div class="auth-header">
                <h2>Completar tu registro</h2>
                <p>Estás a un paso de crear tu cuenta</p>
            </div>

            {{-- 
                Muestra el email de forma informativa (solo lectura). 
                Esto le da seguridad al usuario de que está registrando la cuenta correcta.
            --}}
            <div class="email-display">
                <p style="margin-bottom: 5px; font-size: 13px; color: #7f8c8d;">Registrándote con:</p>
                <strong>{{ $email }}</strong>
            </div>

            <form action="{{ route('register.create') }}" method="POST">
                @csrf
                
                {{-- 
                    TOKEN OCULTO: 
                    Fundamental para vincular estos datos con la invitación enviada por email.
                    Sin este token, el backend rechazaría la creación del usuario.
                --}}
                <input type="hidden" name="token" value="{{ $token }}">

                {{-- Campo: Nombre --}}
                <div class="form-group">
                    <label for="first_name">Nombre</label>
                    <input 
                        type="text" 
                        id="first_name" 
                        name="first_name" 
                        class="form-control @error('first_name') is-invalid @enderror" 
                        value="{{ old('first_name') }}"
                        placeholder="Tu nombre"
                        required
                    >
                    @error('first_name')
                        <span class="form-error">{{ $message }}</span>
                    @enderror
                </div>

                {{-- Campo: Apellidos --}}
                <div class="form-group">
                    <label for="last_name">Apellidos</label>
                    <input 
                        type="text" 
                        id="last_name" 
                        name="last_name" 
                        class="form-control @error('last_name') is-invalid @enderror" 
                        value="{{ old('last_name') }}"
                        placeholder="Tus apellidos"
                        required
                    >
                    @error('last_name')
                        <span class="form-error">{{ $message }}</span>
                    @enderror
                </div>

                {{-- Campo: Fecha de Nacimiento con validación de fecha máxima (hoy) --}}
                <div class="form-group">
                    <label for="birth_date">Fecha de Nacimiento</label>
                    <input 
                        type="date" 
                        id="birth_date" 
                        name="birth_date" 
                        class="form-control @error('birth_date') is-invalid @enderror" 
                        value="{{ old('birth_date') }}"
                        max="{{ date('Y-m-d') }}" {{-- Evita fechas futuras --}}
                        required
                    >
                    @error('birth_date')
                        <span class="form-error">{{ $message }}</span>
                    @enderror
                </div>

                {{-- Campo: Género con persistencia en el Select --}}
                <div class="form-group">
                    <label for="gender">Género</label>
                    <select 
                        id="gender" 
                        name="gender" 
                        class="form-control @error('gender') is-invalid @enderror"
                        required
                    >
                        <option value="" disabled selected>Selecciona tu género</option>
                        {{-- El operador ternario mantiene la selección si hay un error de validación --}}
                        <option value="male" {{ old('gender') == 'male' ? 'selected' : '' }}>Hombre</option>
                        <option value="female" {{ old('gender') == 'female' ? 'selected' : '' }}>Mujer</option>
                        <option value="other" {{ old('gender') == 'other' ? 'selected' : '' }}>Prefiero no decirlo</option>
                    </select>
                    @error('gender')
                        <span class="form-error">{{ $message }}</span>
                    @enderror
                </div>

                {{-- Campo: Contraseña --}}
                <div class="form-group">
                    <label for="password">Contraseña</label>
                    <input 
                        type="password" 
                        id="password" 
                        name="password" 
                        class="form-control @error('password') is-invalid @enderror"
                        placeholder="Mínimo 8 caracteres"
                        required
                    >
                    @error('password')
                        <span class="form-error">{{ $message }}</span>
                    @enderror
                </div>

                {{-- Campo: Confirmación de Contraseña (Requerido por Laravel para validación 'confirmed') --}}
                <div class="form-group">
                    <label for="password_confirmation">Confirmar Contraseña</label>
                    <input 
                        type="password" 
                        id="password_confirmation" 
                        name="password_confirmation" 
                        class="form-control"
                        placeholder="Repite tu contraseña"
                        required
                    >
                </div>

                <button type="submit" class="btn btn-primary">
                    Crear Cuenta
                </button>
            </form>
        </div>
    </div>
</x-layouts.app>