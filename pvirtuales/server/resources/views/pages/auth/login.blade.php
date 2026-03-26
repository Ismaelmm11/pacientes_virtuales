{{--
|--------------------------------------------------------------------------
| Login — Inicio de Sesión
|--------------------------------------------------------------------------
|
| Layout de dos paneles:
| Izquierda → Panel de marca con logo y tagline
| Derecha → Formulario de acceso
|
--}}
<x-layouts.app title="Iniciar Sesión">

    <x-slot:styles>
        <link href="{{ asset('css/auth.css') }}" rel="stylesheet">
    </x-slot:styles>

    <div class="auth-wrapper">

        {{-- ============================================================
        PANEL IZQUIERDO — Branding
        ============================================================ --}}
        <div class="auth-brand">

            {{-- Logo --}}
            <div class="auth-logo">
                <div class="auth-logo-icon">
                    <i data-lucide="activity"></i>
                </div>
                <span class="auth-logo-text">Pacientes<span> Virtuales</span></span>
            </div>

            {{-- Titular --}}
            <h1 class="auth-tagline">
                Aprende medicina<br>
                <span class="auth-tagline-accent">de forma práctica</span>
            </h1>

            <p class="auth-brand-desc">
                Practica entrevistas clínicas con pacientes virtuales impulsados por IA.
                Mejora tus habilidades diagnósticas a tu ritmo.
            </p>

            {{-- Características clave --}}
            <div class="auth-features">
                <div class="auth-feature">
                    <div class="auth-feature-dot"></div>
                    Pacientes virtuales con personalidad propia
                </div>
                <div class="auth-feature">
                    <div class="auth-feature-dot"></div>
                    5 proveedores de IA integrados
                </div>
                <div class="auth-feature">
                    <div class="auth-feature-dot"></div>
                    Tests de evaluación con corrección automática
                </div>
            </div>

        </div>

        {{-- ============================================================
        PANEL DERECHO — Formulario
        ============================================================ --}}
        <div class="auth-form-panel">
            <div class="auth-form-box">

                <h2 class="auth-form-title">Bienvenido de nuevo</h2>
                <p class="auth-form-subtitle">Introduce tus credenciales para acceder a la plataforma</p>

                <form action="{{ route('login.submit') }}" method="POST">
                    @csrf

                    {{-- Email --}}
                    <div class="auth-field">
                        <label for="email">Correo electrónico</label>
                        <input type="email" id="email" name="email" class="@error('email') is-invalid @enderror"
                            value="{{ old('email') }}" placeholder="tu@correo.com" autofocus required>
                        @error('email')
                            <span class="auth-field-error">{{ $message }}</span>
                        @enderror
                    </div>

                    {{-- Contraseña --}}
                    <div class="auth-field">
                        <label for="password">Contraseña</label>
                        <input type="password" id="password" name="password" placeholder="Tu contraseña" required>
                    </div>

                    {{-- Recordarme --}}
                    <div class="auth-remember">
                        <input type="checkbox" id="remember" name="remember">
                        <label for="remember">Mantener sesión iniciada</label>
                    </div>

                    <button type="submit" class="auth-btn">
                        Iniciar Sesión
                    </button>
                </form>

                <div style="text-align: center; margin-top: 20px;">
                    <a href="{{ route('home') }}" class="auth-back-link">
                        ← Volver al inicio
                    </a>
                </div>

            </div>
        </div>

    </div>

</x-layouts.app>