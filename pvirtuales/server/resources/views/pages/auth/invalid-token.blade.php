{{--
|--------------------------------------------------------------------------
| Página de Token Inválido / Expirado
|--------------------------------------------------------------------------
|
| Vista de error controlada. Se muestra cuando la validación del token 
| en el controlador falla, ya sea por tiempo o por alteración del enlace.
|
--}}

<x-layouts.app title="Enlace Inválido">
    {{-- Reutiliza los estilos de autenticación para mantener la coherencia visual --}}
    <x-slot:styles>
        <link href="{{ asset('css/auth.css') }}" rel="stylesheet">
    </x-slot:styles>

    <div class="auth-wrapper">
        <div class="auth-card">
            
            {{-- Indicador visual de advertencia --}}
            <div class="error-icon">
                ⚠️
            </div>
            
            <div class="error-content">
                {{-- Título descriptivo del error --}}
                <h1>Enlace no válido o caducado</h1>
                
                {{-- Explicación amigable para el usuario --}}
                <p>Este enlace de registro no es válido o ya ha expirado.</p>
                
                {{-- 
                    Nota técnica de seguridad: 
                    Informa sobre la política de caducidad (24h) para que el usuario 
                    entienda por qué falló el proceso. 
                --}}
                <p style="color: #95a5a6; font-size: 13px; margin-top: 15px;">
                    Los enlaces de registro son válidos durante 24 horas
                </p>
            </div>

            {{-- 
                Llamada a la acción (CTA): 
                En lugar de solo mostrar el error, redirigimos al usuario al Paso 1 
                para que pueda intentar el proceso de nuevo fácilmente.
            --}}
            <div style="margin-top: 30px;">
                <a href="{{ route('register.start') }}" class="btn btn-primary">
                    Solicitar Nuevo Enlace
                </a>
            </div>

            {{-- Footer con opción de escape al inicio --}}
            <div class="auth-footer">
                <a href="{{ route('home') }}">← Volver al inicio</a>
            </div>
        </div>
    </div>
</x-layouts.app>