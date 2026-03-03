{{--
|--------------------------------------------------------------------------
| Listado de Mis Pacientes Virtuales
|--------------------------------------------------------------------------
|
| Muestra todos los pacientes creados por el usuario actual,
| con opciones para ver el prompt, publicar o eliminar.
|
--}}
<x-layouts.app title="Mis Pacientes">
    <x-slot:styles>
        <link href="{{ asset('css/patients.css') }}" rel="stylesheet">
    </x-slot:styles>

    <x-navbar backRoute="home" />

    <div class="container" style="margin-top: 30px;">
        <div class="header-section">
            <h1>Mis Pacientes Virtuales</h1>
            <a href="{{ route('patients.create') }}" class="btn-create">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="12" y1="5" x2="12" y2="19"/>
                    <line x1="5" y1="12" x2="19" y2="12"/>
                </svg>
                Crear Paciente
            </a>
        </div>

        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        @if($patients->isEmpty())
            <div class="empty-state">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/>
                    <circle cx="9" cy="7" r="4"/>
                </svg>
                <h2>No tienes pacientes creados</h2>
                <p>Crea tu primer paciente virtual para empezar a diseñar casos clínicos</p>
                <a href="{{ route('patients.create') }}" class="btn-create">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <line x1="12" y1="5" x2="12" y2="19"/>
                        <line x1="5" y1="12" x2="19" y2="12"/>
                    </svg>
                    Crear Primer Paciente
                </a>
            </div>
        @else
            <div class="patients-grid">
                @foreach($patients as $patient)
                    <div class="patient-card">
                        <div class="patient-header">
                            <div>
                                <div class="patient-title">{{ $patient->case_title }}</div>
                                <span class="patient-type">{{ $patient->type->name ?? 'General' }}</span>
                            </div>
                            <span class="patient-status {{ $patient->is_published ? 'status-published' : 'status-draft' }}">
                                {{ $patient->is_published ? 'Publicado' : 'Borrador' }}
                            </span>
                        </div>
                        
                        <div class="patient-meta">
                            Creado: {{ $patient->created_at?->format('d/m/Y') ?? 'Fecha no disponible' }}
                        </div>
                        
                        <div class="patient-actions">
                            @if($patient->prompt && $patient->prompt->prompt_content)
                                <a href="{{ route('patients.preview', $patient) }}" class="btn-action btn-preview">
                                    Ver Prompt
                                </a>
                            @else
                                <span class="btn-action" style="background: #ecf0f1; color: #7f8c8d;">
                                    Sin Prompt
                                </span>
                            @endif
                            
                            <form action="{{ route('patients.destroy', $patient) }}" method="POST" style="flex: 1; min-width: 100px;" onsubmit="return confirm('¿Eliminar este paciente?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn-action btn-delete" style="width: 100%;">
                                    Eliminar
                                </button>
                            </form>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</x-layouts.app>