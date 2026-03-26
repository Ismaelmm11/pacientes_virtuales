{{--
|--------------------------------------------------------------------------
| Registro — Paso 2: Completar datos de la cuenta
|--------------------------------------------------------------------------
|
| Solo accesible con token válido recibido por email.
| Recoge los datos personales y la contraseña del nuevo usuario.
|
--}}
<x-layouts.app title="Completar Registro">

    <x-slot:styles>
        <link href="{{ asset('css/auth.css') }}" rel="stylesheet">
    </x-slot:styles>

    <div class="auth-wrapper">

        {{-- ============================================================
        PANEL IZQUIERDO — Branding
        ============================================================ --}}
        <div class="auth-brand">

            <div class="auth-logo">
                <div class="auth-logo-icon">
                    <i data-lucide="activity"></i>
                </div>
                <span class="auth-logo-text">Pacientes<span> Virtuales</span></span>
            </div>

            <h1 class="auth-tagline">
                Casi listo,<br>
                <span class="auth-tagline-accent">un último paso</span>
            </h1>

            <p class="auth-brand-desc">
                Completa tus datos para activar tu cuenta. Solo te llevará un momento.
            </p>

            <div class="auth-features">
                <div class="auth-feature">
                    <div class="auth-feature-dot"></div>
                    Tus datos están protegidos
                </div>
                <div class="auth-feature">
                    <div class="auth-feature-dot"></div>
                    Contraseña mínimo 8 caracteres
                </div>
                <div class="auth-feature">
                    <div class="auth-feature-dot"></div>
                    Podrás cambiar tus datos en cualquier momento
                </div>
            </div>

        </div>

        {{-- ============================================================
        PANEL DERECHO — Formulario
        ============================================================ --}}
        <div class="auth-form-panel">
            <div class="auth-form-box">

                <h2 class="auth-form-title">Completar registro</h2>
                <p class="auth-form-subtitle">Estás registrándote con:</p>

                {{-- Chip con el email del usuario (solo lectura) --}}
                <div class="auth-email-chip">
                    <div class="auth-email-chip-dot"></div>
                    {{ $email }}
                </div>

                <form action="{{ route('register.create') }}" method="POST">
                    @csrf

                    {{-- Token oculto: vincula estos datos con la invitación por email --}}
                    <input type="hidden" name="token" value="{{ $token }}">

                    {{-- Nombre y apellidos en dos columnas --}}
                    <div class="auth-fields-row">
                        <div class="auth-field">
                            <label for="first_name">Nombre</label>
                            <input type="text"
                                   id="first_name"
                                   name="first_name"
                                   class="@error('first_name') is-invalid @enderror"
                                   value="{{ old('first_name') }}"
                                   placeholder="Tu nombre"
                                   autofocus
                                   required>
                            @error('first_name')
                                <span class="auth-field-error">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="auth-field">
                            <label for="last_name">Apellidos</label>
                            <input type="text"
                                   id="last_name"
                                   name="last_name"
                                   class="@error('last_name') is-invalid @enderror"
                                   value="{{ old('last_name') }}"
                                   placeholder="Tus apellidos"
                                   required>
                            @error('last_name')
                                <span class="auth-field-error">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    {{-- Fecha de nacimiento --}}
                    <div class="auth-field">
                        <label for="birth_date">Fecha de nacimiento</label>
                        <input type="date"
                               id="birth_date"
                               name="birth_date"
                               class="@error('birth_date') is-invalid @enderror"
                               value="{{ old('birth_date') }}"
                               max="{{ date('Y-m-d') }}"
                               required>
                        @error('birth_date')
                            <span class="auth-field-error">{{ $message }}</span>
                        @enderror
                    </div>

                    {{-- Género --}}
                    <div class="auth-field">
                        <label for="gender">Género</label>
                        <select id="gender"
                                name="gender"
                                class="@error('gender') is-invalid @enderror"
                                required>
                            <option value="" disabled {{ old('gender') ? '' : 'selected' }}>
                                Selecciona una opción
                            </option>
                            <option value="male"   {{ old('gender') === 'male'   ? 'selected' : '' }}>Hombre</option>
                            <option value="female" {{ old('gender') === 'female' ? 'selected' : '' }}>Mujer</option>
                            <option value="other"  {{ old('gender') === 'other'  ? 'selected' : '' }}>Prefiero no decirlo</option>
                        </select>
                        @error('gender')
                            <span class="auth-field-error">{{ $message }}</span>
                        @enderror
                    </div>

                    {{-- Contraseña --}}
                    <div class="auth-field">
                        <label for="password">Contraseña</label>
                        <input type="password"
                               id="password"
                               name="password"
                               class="@error('password') is-invalid @enderror"
                               placeholder="Mínimo 8 caracteres"
                               required>
                        @error('password')
                            <span class="auth-field-error">{{ $message }}</span>
                        @enderror
                    </div>

                    {{-- Confirmación de contraseña --}}
                    <div class="auth-field">
                        <label for="password_confirmation">Confirmar contraseña</label>
                        <input type="password"
                               id="password_confirmation"
                               name="password_confirmation"
                               placeholder="Repite tu contraseña"
                               required>
                    </div>

                    <button type="submit" class="auth-btn">
                        Crear Cuenta
                    </button>
                </form>

            </div>
        </div>

    </div>

</x-layouts.app>
