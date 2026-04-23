{{--
|--------------------------------------------------------------------------
| Crear Paciente Virtual — Modo Básico
|--------------------------------------------------------------------------
|
| Vista contenedora del formulario básico de creación de pacientes.
| Organiza las 5 secciones modulares y gestiona el envío al controlador.
|
| SECCIONES:
| 1. section-case → Título, descripción y objetivos del caso
| 2. section-identity → Identidad del paciente o acompañante
| 3. section-clinical → Consulta, síntomas, medicación, diagnóstico
| 4. section-personality → Personalidad, verbosidad, conocimiento médico
| 5. section-extra → Instrucciones especiales, frases límite
|
--}}
<x-layouts.app>

    {{-- Título de la pestaña --}}

    <x-slot name="title">Editar Paciente — Modo Básico</x-slot>

    <x-slot name="styles">
        <link href="{{ asset('css/create-patient.css') }}" rel="stylesheet">
        <link rel="stylesheet" href="{{ asset('css/modal.css') }}">
    </x-slot>

    <x-slot name="topbar">
        <div class="topbar">
            <div class="topbar-left">
                <div class="topbar-title">Editar Paciente - {{ $patient->name }}</div>
                <div class="topbar-subtitle">
                    <span class="mode-badge">
                        <i data-lucide="zap"></i>
                        Modo Básico
                    </span>
                </div>
            </div>
            <div class="topbar-right">
                <a href="{{ route('teacher.patients.preview', $patient) }}{{ request('origen') ? '?origen=' . request('origen') : '' }}"
                    class="btn btn-ghost btn-sm">
                    <i data-lucide="x"></i>
                    Cancelar
                    </a>
            </div>
        </div>
    </x-slot>

    <div class="create-patient-layout">

        {{-- ===== COLUMNA PRINCIPAL: FORMULARIO ===== --}}
        <div class="create-patient-main">

            {{-- Errores de validación --}}
            @if($errors->any())
                <div class="cp-alert-error" id="validationErrors">
                    <div class="cp-alert-icon">
                        <i data-lucide="circle-alert"></i>
                    </div>
                    <div class="cp-alert-body">
                        <strong>Corrige los siguientes errores antes de continuar:</strong>
                        <ul>
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            @endif

            {{-- Formulario --}}
            <form action="{{ route('teacher.patients.update', $patient) }}{{ request('origen') ? '?origen=' . request('origen') : '' }}" method="POST" id="patientForm">
                @csrf
                @method('PUT')
                <input type="hidden" name="mode" value="basic">

                @include('pages.patients.partials.basic.section-case')
                @include('pages.patients.partials.basic.section-identity')
                @include('pages.patients.partials.basic.section-clinical')
                @include('pages.patients.partials.basic.section-personality')
                @include('pages.patients.partials.basic.section-extra')

                {{-- Botonera --}}
                <div class="cp-form-actions">
                    <a href="{{ route('teacher.patients.preview', $patient) }}{{ request('origen') ? '?origen=' . request('origen') : '' }}"
                        class="btn btn-ghost">
                        Cancelar
                    </a>

                    <button type="submit" class="btn btn-primary btn-lg">
                        <i data-lucide="sparkles"></i>
                        Guardar Cambios
                    </button>
                </div>

            </form>
        </div>

        {{-- ===== COLUMNA LATERAL: ÍNDICE DE SECCIONES ===== --}}
        <aside class="create-patient-sidebar">
            <div class="cp-sidebar-card">
                <div class="cp-sidebar-title">Secciones</div>
                <nav class="cp-sidebar-nav">
                    <a href="#section-case" class="cp-sidebar-link active">
                        <i data-lucide="file-text"></i>
                        Información del Caso
                    </a>
                    <a href="#section-identity" class="cp-sidebar-link">
                        <i data-lucide="user-round"></i>
                        Identidad
                    </a>
                    <a href="#section-clinical" class="cp-sidebar-link">
                        <i data-lucide="activity"></i>
                        La Consulta
                    </a>
                    <a href="#section-personality" class="cp-sidebar-link">
                        <i data-lucide="smile"></i>
                        Personalidad
                    </a>
                    <a href="#section-extra" class="cp-sidebar-link">
                        <i data-lucide="settings"></i>
                        Configuración Extra
                    </a>
                </nav>
            </div>

            {{-- Info del modo --}}
            <div class="cp-sidebar-card cp-sidebar-info">
                <div class="cp-sidebar-info-icon">
                    <i data-lucide="zap"></i>
                </div>
                <div class="cp-sidebar-info-title">Modo Básico</div>
                <div class="cp-sidebar-info-text">
                    Rellena los campos y el sistema generará automáticamente el prompt para la IA.
                    Los campos con <span class="required">*</span> son obligatorios.
                </div>
            </div>
        </aside>

    </div>

    {{-- Modal de confirmación al quitar texto personalizado --}}
    <div class="sim-modal-overlay" id="customTextModal">
        <div class="sim-modal">
            <div class="sim-modal-icon" style="background: rgba(231,76,60,0.12); color: var(--color-danger);">
                <i data-lucide="alert-triangle" style="width:26px;height:26px;"></i>
            </div>
            <div class="sim-modal-title">¿Volver al texto automático?</div>
            <p class="sim-modal-body">Se perderá el texto personalizado que has escrito. Esta acción no se puede
                deshacer.</p>
            <div class="sim-modal-actions">
                <button class="btn btn-ghost btn-sm" onclick="closeCustomTextModal()">Cancelar</button>
                <button class="btn btn-primary btn-sm" id="btnConfirmCustomText">Confirmar</button>
            </div>
        </div>
    </div>


    <x-slot name="scripts">
        <script src="{{ asset('js/create-patient-basic.js') }}"></script>
        {{-- Scroll activo en el sidebar según la sección visible --}}
        <script>
            // Inicializar contadores al número de filas ya renderizadas
            symptomCount = {{ count(old('symptoms', [[]])) }};
            medicationCount = {{ count(old('medications', [])) }};
            viceCount = {{ count(old('vices', [])) }};
            fraseLimiteCount = {{ count(old('frases_limite', [])) }};
        </script>
        <script>
            (function () {
                const links = document.querySelectorAll('.cp-sidebar-link');
                const sections = document.querySelectorAll('.cp-section');

                if (!sections.length) return;

                const observer = new IntersectionObserver((entries) => {
                    entries.forEach(entry => {
                        if (entry.isIntersecting) {
                            links.forEach(l => l.classList.remove('active'));
                            const active = document.querySelector(`.cp-sidebar-link[href="#${entry.target.id}"]`);
                            if (active) active.classList.add('active');
                        }
                    });
                }, { threshold: 0.3 });

                sections.forEach(s => observer.observe(s));

                // Scroll a errores si los hay
                const errors = document.getElementById('validationErrors');
                if (errors) errors.scrollIntoView({ behavior: 'smooth', block: 'center' });
            })();
        </script>
    </x-slot>

    </x-app-layout>