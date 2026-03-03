{{--
|--------------------------------------------------------------------------
| Página de Inicio de Sesión
|--------------------------------------------------------------------------
|
| Formulario de autenticación para usuarios registrados.
| Usa el layout maestro y el CSS de autenticación.
|
--}}
<x-layouts.app title="Iniciar Sesión">
    {{-- CSS específico de páginas de auth --}}
    <x-slot:styles>
        <link href="{{ asset('css/auth.css') }}" rel="stylesheet">
    </x-slot:styles>

    <div class="auth-wrapper">
        <div class="auth-card">
            <div class="auth-header">
                <h2>Bienvenido de nuevo</h2>
                <p>Introduce tus credenciales para acceder</p>
            </div>

            <form action="{{ route('login.submit') }}" method="POST">
                @csrf
                
                <div class="form-group">
                    <label for="email">Correo Electrónico</label>
                    <input 
                        type="email" 
                        id="email" 
                        name="email" 
                        class="form-control @error('email') is-invalid @enderror" 
                        value="{{ old('email') }}"
                        required
                        autofocus
                    >
                    @error('email')
                        <span class="form-error">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="password">Contraseña</label>
                    <input 
                        type="password" 
                        id="password" 
                        name="password" 
                        class="form-control"
                        required
                    >
                </div>

                <div style="margin-bottom: 20px; display: flex; align-items: center; gap: 8px;">
                    <input type="checkbox" id="remember" name="remember">
                    <label for="remember" style="margin: 0; font-weight: normal; font-size: 14px;">Mantener sesión iniciada</label>
                </div>

                <button type="submit" class="btn btn-primary">
                    Iniciar Sesión
                </button>
            </form>

            <div class="auth-footer">
                <p>¿No tienes cuenta? <a href="{{ route('register.start') }}">Regístrate aquí</a></p>
                <br>
                <a href="{{ route('home') }}">← Volver al inicio</a>
            </div>
        </div>
    </div>
</x-layouts.app>