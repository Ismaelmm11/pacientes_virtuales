{{--
|--------------------------------------------------------------------------
| Selección de Modo — Crear Paciente Virtual
|--------------------------------------------------------------------------
|
| Pantalla de elección entre Modo Básico y Modo Avanzado.
| Dos mitades gigantes que ocupan toda la pantalla disponible.
|
--}}
<x-layouts.app>

    {{-- Título de la pestaña --}}

    <x-slot name="title">Crear Paciente Virtual</x-slot>

    <x-slot name="styles">
        <link
            href="https://fonts.googleapis.com/css2?family=Syne:wght@700;800&family=DM+Sans:wght@400;500;600&display=swap"
            rel="stylesheet">
        <link href="{{ asset('css/select-mode.css') }}" rel="stylesheet">
    </x-slot>

    <x-slot name="topbar">
        <div class="topbar">
            <div class="topbar-left">
                <div class="topbar-title">Nuevo Paciente</div>
                <div class="topbar-subtitle">Elige el modo de creación</div>
            </div>
            <div class="topbar-right">
                @if(request('origen') !== 'admin')
                    @php
                        $backRoute = request('origen') === 'dashboard'
                            ? route('teacher.dashboard')
                            : route('teacher.patients.index');

                        $backLabel = request('origen') === 'dashboard'
                            ? 'Dashboard'
                            : 'Mis Pacientes';
                    @endphp
                @else
                    @php
                        $backRoute = route('admin.patients.index');
                        $backLabel = 'Volver a la lista de Pacientes';
                    @endphp
                @endif
                {{-- DESPUÉS --}}


                <a href="{{ $backRoute }}" class="btn btn-ghost btn-sm">
                    <i data-lucide="arrow-left"></i>
                    {{ $backLabel }}
                </a>
            </div>
        </div>
    </x-slot>

    <div class="mode-split">

        {{-- ===== MODO BÁSICO ===== --}}
        <a href="{{ route('teacher.patients.create.basic') }}{{ request('origen') ? '?origen=' . request('origen') : '' }}" class="mode-half mode-basic">
            <div class="mode-half-blob" aria-hidden="true"></div>
            <div class="mode-half-inner">
                <div class="mode-half-tag">
                    <i data-lucide="zap"></i>
                    Recomendado
                </div>
                <h2 class="mode-half-title">Modo<br>Básico</h2>
                <p class="mode-half-desc">
                    Formulario guiado de 5 secciones.<br>
                    Listo en 5–10 minutos.
                </p>
                <ul class="mode-half-features">
                    <li><i data-lucide="check"></i> Identidad y contexto del paciente</li>
                    <li><i data-lucide="check"></i> Síntomas con reglas de revelación</li>
                    <li><i data-lucide="check"></i> 11 personalidades con preview</li>
                    <li><i data-lucide="check"></i> Verbosidad y conocimiento médico</li>
                    <li><i data-lucide="check"></i> Preocupaciones ocultas</li>
                </ul>
                <div class="mode-half-cta">
                    Empezar
                    <i data-lucide="arrow-right"></i>
                </div>
            </div>
        </a>

        {{-- Divisor central --}}
        <div class="mode-divider"><span>o</span></div>

        {{-- ===== MODO AVANZADO ===== --}}
        <a href="#" class="mode-half mode-advanced">
            <div class="mode-half-blob" aria-hidden="true"></div>
            <div class="mode-half-inner">
                <div class="mode-half-tag">
                    <i data-lucide="flask-conical"></i>
                    Control total
                </div>
                <h2 class="mode-half-title">Modo<br>Avanzado</h2>
                <p class="mode-half-desc">
                    Wizard de 5 pasos con máximo detalle.<br>
                    Listo en 15–30 minutos.
                </p>
                <ul class="mode-half-features">
                    <li><i data-lucide="check"></i> Todo lo del Modo Básico</li>
                    <li><i data-lucide="check"></i> Medicación con adherencia</li>
                    <li><i data-lucide="check"></i> Contradicciones intencionales</li>
                    <li><i data-lucide="check"></i> Reglas Si/Entonces para la IA</li>
                    <li><i data-lucide="check"></i> Gatillos emocionales y cierre</li>
                </ul>
                <div class="mode-half-cta">
                    Empezar
                    <i data-lucide="arrow-right"></i>
                </div>
            </div>
        </a>

    </div>

</x-layouts.app>