<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Enlace Inválido - Pacientes Virtuales</title>
    <link href="{{ asset('css/style.css') }}" rel="stylesheet">
    <link href="{{ asset('css/auth.css') }}" rel="stylesheet">
</head>
<body>
    <div class="auth-wrapper">
        <div class="auth-card">
            <div class="error-icon">
                ⚠️
            </div>
            
            <div class="error-content">
                <h1>Enlace no válido o caducado</h1>
                <p>Este enlace de registro no es válido o ya ha expirado.</p>
                <p style="color: #95a5a6; font-size: 13px; margin-top: 15px;">
                    Los enlaces de registro son válidos durante 24 horas
                </p>
            </div>

            <div style="margin-top: 30px;">
                <a href="{{ route('register.start') }}" class="btn btn-primary">
                    Solicitar Nuevo Enlace
                </a>
            </div>

            <div class="auth-footer">
                <a href="/pacientes_virtuales">← Volver al inicio</a>
            </div>
        </div>
    </div>
</body>
</html>