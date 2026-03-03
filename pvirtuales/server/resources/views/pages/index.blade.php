{{--
|--------------------------------------------------------------------------
| Página de Inicio — Bienvenida pública
|--------------------------------------------------------------------------
|
| Vista pública para usuarios no autenticados.
| Los autenticados son redirigidos en HomeController a su dashboard.
|
| Estética: título grande centrado + cards inclinadas en la parte inferior
| inspiradas en el diseño tipo HORMN.
|
--}}
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pacientes Virtuales — SimulAI</title>

    {{-- Google Fonts: Syne para títulos (bold, editorial) + DM Sans para cuerpo --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@700;800&family=DM+Sans:wght@400;500;600&display=swap" rel="stylesheet">

    <link href="{{ asset('css/style.css') }}" rel="stylesheet">
    <link href="{{ asset('css/home.css') }}" rel="stylesheet">
</head>
<body class="home-body">

    {{-- ========================================================
         NAVBAR — Fija, fondo cristal
         ======================================================== --}}
    <nav class="home-nav">
        <div class="home-nav-inner">

            {{-- Logo --}}
            <a href="{{ route('home') }}" class="home-nav-logo">
                <div class="home-nav-logo-icon">
                    <i data-lucide="activity"></i>
                </div>
                <span>Pacientes<strong>Virtuales</strong></span>
            </a>

            {{-- Acciones --}}
            <div class="home-nav-actions">
                <a href="{{ route('login') }}" class="btn btn-ghost btn-sm">
                    Iniciar Sesión
                </a>
                <a href="{{ route('register.start') }}" class="btn btn-primary btn-sm">
                    Solicitar Acceso
                    <i data-lucide="arrow-right"></i>
                </a>
            </div>
        </div>
    </nav>

    {{-- ========================================================
         HERO — Título grande + subtítulo + CTA
         ======================================================== --}}
    <section class="home-hero">

        {{-- Blobs de fondo --}}
        <div class="home-blob home-blob-1" aria-hidden="true"></div>
        <div class="home-blob home-blob-2" aria-hidden="true"></div>
        <div class="home-blob home-blob-3" aria-hidden="true"></div>

        <div class="home-hero-inner">

            {{-- Badge --}}
            <div class="home-hero-badge">
                <i data-lucide="sparkles"></i>
                Simulación clínica con IA
            </div>

            {{-- Título --}}
            <h1 class="home-hero-title">
                Pacientes<br>
                <span class="home-hero-title-accent">Virtuales.</span>
            </h1>

            {{-- Subtítulo --}}
            <p class="home-hero-subtitle">
                Practica entrevistas clínicas reales con pacientes simulados por inteligencia artificial.
                Entrena tu anamnesis, detecta síntomas ocultos y recibe evaluación inmediata,
                todo en un entorno seguro diseñado para estudiantes de medicina.
            </p>

            {{-- CTAs --}}
            <div class="home-hero-ctas">
                <a href="{{ route('register.start') }}" class="btn btn-primary btn-lg">
                    Solicitar Acceso
                    <i data-lucide="arrow-right"></i>
                </a>
                <a href="{{ route('login') }}" class="btn btn-ghost btn-lg">
                    Ya tengo cuenta
                </a>
            </div>

        </div>
    </section>

    {{-- ========================================================
         CARDS INCLINADAS — Sección inferior tipo HORMN
         ======================================================== --}}
    <section class="home-cards-section">
        
    </section>

    {{-- ========================================================
         FOOTER
         ======================================================== --}}
    <footer class="home-footer">
        <div class="home-footer-inner">
            <span>© {{ date('Y') }} Pacientes Virtuales — Plataforma educativa de simulación clínica</span>
            <div class="home-footer-links">
                <a href="{{ route('login') }}">Acceder</a>
                <a href="{{ route('register.start') }}">Solicitar Acceso</a>
            </div>
        </div>
    </footer>

    {{-- Lucide Icons --}}
    <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.min.js"></script>
    <script>lucide.createIcons();</script>

</body>
</html>