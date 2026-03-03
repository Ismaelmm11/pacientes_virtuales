{{--
|--------------------------------------------------------------------------
| Página de Pre-Registro (Paso 1)
|--------------------------------------------------------------------------
|
| Esta vista representa el inicio del flujo de registro. En lugar de crear
| el usuario directamente, se solicita el email para validar la identidad.
|
--}}

<x-layouts.app title="Iniciar Registro">
    {{-- Carga de estilos específicos para los formularios de autenticación --}}
    <x-slot:styles>
        <link href="{{ asset('css/auth.css') }}" rel="stylesheet">
    </x-slot:styles>

    <div class="auth-wrapper">
        <div class="auth-card">
            {{-- Encabezado de la tarjeta de registro --}}
            <div class="auth-header">
                <h2>Crear una nueva cuenta</h2>
                <p>Introduce tu correo electrónico para recibir un enlace de registro seguro</p>
            </div>

            {{-- 
                MENSAJE DE ÉXITO: 
                Muestra la notificación si el controlador redirigió con una sesión 'success'. 
                Utiliza la clase .message.success que definimos en los estilos globales.
            --}}
            @if (session('success'))
                <div class="message success">
                    {{ session('success') }}
                </div>
            @endif

            {{-- Formulario que apunta al método del controlador que genera el link --}}
            <form action="{{ route('register.sendlink') }}" method="POST">
                {{-- Token de seguridad obligatorio en Laravel para formularios POST --}}
                @csrf
                
                <div class="form-group">
                    <label for="email">Correo Electrónico</label>
                    <input 
                        type="email" 
                        id="email" 
                        name="email" 
                        {{-- 
                            Uso de la directiva @error para añadir clases CSS dinámicamente 
                            si la validación falla en el servidor.
                        --}}
                        class="form-control @error('email') is-invalid @enderror" 
                        {{-- old('email') recupera el texto escrito si hubo un error al enviar --}}
                        value="{{ old('email') }}"
                        placeholder="ejemplo@correo.com"
                        required
                    >

                    {{-- 
                        MENSAJE DE ERROR:
                        Se muestra solo si el campo 'email' falla en la validación (ej: no es un email o ya existe).
                    --}}
                    @error('email')
                        <span class="form-error">{{ $message }}</span>
                    @enderror
                </div>

                {{-- Botón de acción principal --}}
                <button type="submit" class="btn btn-primary">
                    Enviar Enlace de Registro
                </button>
            </form>

            {{-- Enlace de retorno para navegación sencilla --}}
            <div class="auth-footer">
                <a href="{{ route('home') }}">← Volver al inicio</a>
            </div>
        </div>
    </div>
</x-layouts.app>