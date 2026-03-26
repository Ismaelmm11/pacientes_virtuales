{{--
|--------------------------------------------------------------------------
| Crear Paciente Virtual (Modo Avanzado) — Wizard
|--------------------------------------------------------------------------
|
| Este componente utiliza un patrón de diseño "Wizard" para segmentar 
| la creación compleja de un prompt en pasos digeribles.
|
--}}
<x-layouts.app title="Crear Paciente — Modo Avanzado">
    <x-slot:styles>
        {{-- Estilos compartidos y estilos específicos para el sistema de pasos --}}
        <link href="{{ asset('css/create-patient.css') }}" rel="stylesheet">
        <link href="{{ asset('css/create-patient-advanced.css') }}" rel="stylesheet">
    </x-slot:styles>

    <div class="create-patient-wrapper">

        <x-navbar backRoute="teacher.patients.index" backLabel="Volver a Mis Pacientes" rightLabel="Modo Avanzado" />
        
        <div class="form-container">
            <div class="form-header">
                <h1>🩺 Crear Paciente Virtual — Modo Avanzado</h1>
                <p>Control total sobre el comportamiento del paciente. Navega entre los 5 pasos con los botones o la barra de progreso.</p>
            </div>

            {{-- 
                BARRA DE PROGRESO (Step Indicator):
                Crucial para que el usuario sepa cuánto le falta. 
                El atributo 'data-step' permite que el JS navegue al hacer clic.
            --}}
            <div class="wizard-progress">
                <div class="wizard-step-indicator active" data-step="0">
                    <span class="wizard-step-number">1</span>
                    <span class="wizard-step-label">El Caso</span>
                </div>
                <div class="wizard-step-indicator" data-step="1">
                    <span class="wizard-step-number">2</span>
                    <span class="wizard-step-label">Identidad</span>
                </div>
                <div class="wizard-step-indicator" data-step="2">
                    <span class="wizard-step-number">3</span>
                    <span class="wizard-step-label">Cuadro Clínico</span>
                </div>
                <div class="wizard-step-indicator" data-step="3">
                    <span class="wizard-step-number">4</span>
                    <span class="wizard-step-label">Psicología</span>
                </div>
                <div class="wizard-step-indicator" data-step="4">
                    <span class="wizard-step-number">5</span>
                    <span class="wizard-step-label">Lógica</span>
                </div>
            </div>

            <div class="form-body">
                @if($errors->any())
                    <div class="alert-danger">
                        <strong>Por favor corrige los siguientes errores:</strong>
                        <ul>
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('patients.store') }}" method="POST" id="patientForm">
                    @csrf
                    {{-- Indicamos al backend que use el motor de generación avanzada --}}
                    <input type="hidden" name="mode" value="advanced">

                    {{-- 
                        PANELES DEL WIZARD:
                        Cada panel se muestra/oculta mediante JS usando la clase 'active'.
                        Nota la modularidad: cada paso es un archivo Blade independiente.
                    --}}

                    {{-- PASO 1: Contexto Pedagógico --}}
                    <div class="wizard-panel active">
                        @include('pages.patients.partials.advanced.step-case')
                        @include('pages.patients.partials.advanced._wizard-nav')
                    </div>

                    {{-- PASO 2: Datos Demográficos profundos --}}
                    <div class="wizard-panel">
                        @include('pages.patients.partials.advanced.step-identity')
                        @include('pages.patients.partials.advanced._wizard-nav')
                    </div>

                    {{-- PASO 3: Fisiopatología y Síntomas --}}
                    <div class="wizard-panel">
                        @include('pages.patients.partials.advanced.step-clinical')
                        @include('pages.patients.partials.advanced._wizard-nav')
                    </div>

                    {{-- PASO 4: Perfil Cognitivo-Conductual --}}
                    <div class="wizard-panel">
                        @include('pages.patients.partials.advanced.step-psychology')
                        @include('pages.patients.partials.advanced._wizard-nav')
                    </div>

                    {{-- PASO 5: Árbol de Lógica y Gatillos del Prompt --}}
                    <div class="wizard-panel">
                        @include('pages.patients.partials.advanced.step-logic')
                        @include('pages.patients.partials.advanced._wizard-nav')
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Script que gestiona el cambio de paneles, validación por paso y la barra de progreso --}}
    <x-slot:scripts>
        <script src="{{ asset('js/create-patient-advanced.js') }}"></script>
    </x-slot:scripts>
</x-layouts.app>