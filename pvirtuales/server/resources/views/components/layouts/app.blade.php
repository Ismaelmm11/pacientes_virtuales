{{--
|--------------------------------------------------------------------------
| Layout Principal — SimulAI
|--------------------------------------------------------------------------
|
| Layout maestro que heredan TODAS las vistas autenticadas.
|
| SLOTS:
|   $title    → Título de la pestaña del navegador
|   $styles   → CSS adicionales específicos de la página
|   $topbar   → Topbar superior (opcional, se define en cada vista)
|   $slot     → Contenido principal
|   $scripts  → JS al final del body
|
--}}
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    {{-- Token CSRF para peticiones AJAX (chat, formularios dinámicos) --}}
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? 'Inicio' }} — SimulAI</title>

    {{-- CSS base global --}}
    <link href="{{ asset('css/style.css') }}" rel="stylesheet">

    {{-- CSS específico de la vista hija --}}
    {{ $styles ?? '' }}
</head>
<body>

<div class="app-wrapper">

    {{-- Sidebar de navegación (solo para usuarios autenticados) --}}
    @auth
        @include('components.sidebar')
    @endauth

    {{-- Área de contenido principal --}}
    <div class="app-content">

        {{-- Topbar superior — se define en cada vista con x-slot:topbar --}}
        @isset($topbar)
            {{ $topbar }}
        @endisset

        {{-- Contenido de la vista con padding estándar --}}
        <div class="content-area">

            {{-- Mensaje flash de éxito --}}
            @if(session('success'))
                <div class="alert alert-success">
                    <i data-lucide="check-circle"></i>
                    {{ session('success') }}
                </div>
            @endif

            {{-- Mensaje flash de error --}}
            @if(session('error'))
                <div class="alert alert-danger">
                    <i data-lucide="alert-circle"></i>
                    {{ session('error') }}
                </div>
            @endif

            {{-- Contenido principal de la vista hija --}}
            {{ $slot }}

        </div>
    </div>
</div>

{{-- Lucide Icons — renderiza todos los data-lucide del DOM --}}
<script src="https://unpkg.com/lucide@latest/dist/umd/lucide.min.js"></script>
<script>lucide.createIcons();</script>

{{-- JS específico de la vista hija --}}
{{ $scripts ?? '' }}

</body>
</html>