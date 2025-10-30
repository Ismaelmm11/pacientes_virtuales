<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Registro - Pacientes Virtuales</title>
    <link href="{{ asset('css/style.css') }}" rel="stylesheet">
    <link href="{{ asset('css/auth.css') }}" rel="stylesheet">
</head>
<body>
    <div class="auth-wrapper">
        <div class="auth-card">
            <div class="auth-header">
                <h2>Crear una nueva cuenta</h2>
                <p>Introduce tu correo electrónico para recibir un enlace de registro seguro</p>
            </div>

            <!-- Mostrar mensaje de éxito -->
            @if (session('success'))
                <div class="message success">
                    {{ session('success') }}
                </div>
            @endif

            <form action="{{ route('register.sendlink') }}" method="POST">
                @csrf
                
                <div class="form-group">
                    <label for="email">Correo Electrónico</label>
                    <input 
                        type="email" 
                        id="email" 
                        name="email" 
                        class="form-control @error('email') is-invalid @enderror" 
                        value="{{ old('email') }}"
                        placeholder="ejemplo@correo.com"
                        required
                    >
                    
                    <!-- Mostrar errores de validación -->
                    @error('email')
                        <span class="form-error">{{ $message }}</span>
                    @enderror
                </div>

                <button type="submit" class="btn btn-primary">
                    Enviar Enlace de Registro
                </button>
            </form>

            <div class="auth-footer">
                <a href="/">← Volver al inicio</a>
            </div>
        </div>
    </div>
</body>
</html>