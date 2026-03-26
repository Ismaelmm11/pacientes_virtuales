{{--
|--------------------------------------------------------------------------
| Dashboard del Profesor
|--------------------------------------------------------------------------
|
| Vista principal del profesor tras el login.
| Usa el layout app.blade.php (sidebar + topbar + content-area).
|
| SECCIONES:
| 1. Stats: 4 métricas clave en tarjetas
| 2. Pacientes recientes: tabla con los últimos 6 pacientes
| 3. Acceso rápido: botón de crear paciente si no tiene ninguno
|
| DATOS RECIBIDOS DEL CONTROLADOR:
| $totalPatients → int
| $publishedPatients → int
| $draftPatients → int
| $totalConsultations → int
| $recentPatients → Collection de Patient
|
--}}

<x-layouts.app>

    {{-- Título de la pestaña --}}
    <x-slot name="title">Dashboard</x-slot>

    {{-- CSS específico de esta vista --}}
    <x-slot name="styles">
        <link href="{{ asset('css/dashboard.css') }}" rel="stylesheet">
    </x-slot>

    {{-- Topbar --}}
    <x-slot name="topbar">
        <div class="topbar">
            <div class="topbar-left">
                <div class="topbar-title">
                    Bienvenido, {{ Auth::user()->first_name }} 👋
                </div>
                <div class="topbar-subtitle">
                    {{ now()->isoFormat('dddd, D [de] MMMM [de] YYYY') }}
                </div>
            </div>
            <div class="topbar-right">
                {{-- Botón de acción principal: crear paciente --}}
                <a href="{{ route('teacher.patients.create') }}?origen=dashboard" class="btn btn-primary btn-sm">
                    <i data-lucide="plus"></i>
                    Nuevo Paciente
                </a>
            </div>
        </div>
    </x-slot>

    {{-- ================================================================
    CONTENIDO PRINCIPAL
    ================================================================ --}}

    {{-- ---- 1. STATS ---- --}}
    <div class="stats-grid">

        <div class="stat-card">
            <div class="stat-card-icon primary">
                <i data-lucide="book-open"></i>
            </div>
            <div class="stat-card-info">
                <div class="stat-card-value">{{ $totalSubjects }}</div>
                <div class="stat-card-label">Asignaturas</div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-card-icon secondary">
                <i data-lucide="users"></i>
            </div>
            <div class="stat-card-info">
                <div class="stat-card-value">{{ $totalStudents }}</div>
                <div class="stat-card-label">Alumnos</div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-card-icon warning">
                <i data-lucide="user-round"></i>
            </div>
            <div class="stat-card-info">
                <div class="stat-card-value">{{ $totalPatients }}</div>
                <div class="stat-card-label">Pacientes creados</div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-card-icon danger">
                <i data-lucide="message-square"></i>
            </div>
            <div class="stat-card-info">
                <div class="stat-card-value">{{ $totalConsultations }}</div>
                <div class="stat-card-label">Simulaciones realizadas</div>
            </div>
        </div>

    </div>

    {{-- ---- 2. PACIENTES RECIENTES ---- --}}
    <div class="card mt-lg">

        <div class="card-header">
            <div class="card-header-title">
                <i data-lucide="clock"></i>
                Pacientes recientes
            </div>
            @if($totalPatients > 0)
                <a href="{{ route('teacher.patients.index') }}" class="btn btn-ghost btn-sm">
                    Ver todos
                    <i data-lucide="arrow-right"></i>
                </a>
            @endif
        </div>

        @if($recentPatients->isEmpty())
            {{-- Estado vacío: aún no ha creado ningún paciente --}}
            <div class="empty-state">
                <div class="empty-state-icon">
                    <i data-lucide="user-round-x"></i>
                </div>
                <div class="empty-state-title">Aún no tienes pacientes</div>
                <div class="empty-state-text">
                    Crea tu primer paciente virtual para que tus alumnos puedan practicar entrevistas clínicas.
                </div>
                <a href="{{ route('teacher.patients.create') }}" class="btn btn-primary mt-md">
                    <i data-lucide="plus"></i>
                    Crear primer paciente
                </a>
            </div>

        @else
            {{-- Tabla de pacientes recientes --}}
            <div class="table-wrapper" style="border: none; border-radius: 0;">
                <table>
                    <thead>
                        <tr>
                            <th>Paciente</th>
                            <th>Modo</th>
                            <th>Asignatura</th>
                            <th>Estado</th>
                            <th>Creado</th>
                            <th class="actions">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($recentPatients as $patient)
                            <tr>
                                {{-- Nombre del paciente y descripción --}}
                                <td>
                                    <div class="patient-name">
                                        {{ $patient->case_title }}
                                    </div>
                                    @if($patient->patient_description)
                                        <div class="patient-desc">
                                            {{ $patient->patient_description }}
                                        </div>
                                    @endif
                                </td>

                                {{-- Modo: básico o avanzado --}}
                                <td>
                                    @if($patient->mode === 'basic')
                                        <span class="badge badge-secondary">Básico</span>
                                    @else
                                        <span class="badge badge-primary">Avanzado</span>
                                    @endif
                                </td>

                                {{-- Asignatura --}}
                                <td>
                                    <span class="text-muted text-sm">
                                        {{ $patient->subject?->name ?? '—' }}
                                    </span>
                                </td>

                                {{-- Estado: publicado o borrador --}}
                                <td>
                                    @if($patient->is_published)
                                        <span class="badge badge-success">
                                            <i data-lucide="check"></i>
                                            Publicado
                                        </span>
                                    @else
                                        <span class="badge badge-warning">
                                            <i data-lucide="clock"></i>
                                            Borrador
                                        </span>
                                    @endif
                                </td>

                                {{-- Fecha de creación --}}
                                <td class="text-muted text-sm">
                                    {{ $patient->created_at->diffForHumans() }}
                                </td>

                                {{-- Acciones --}}
                                <td class="actions">
                                    <div class="row-actions">

                                        {{-- Previsualizar prompt --}}
                                        <a href="{{ route('teacher.patients.preview', $patient) }}" class="btn-action"
                                            title="Previsualizar prompt">
                                            <i data-lucide="eye"></i>
                                        </a>

                                        {{-- Gestionar test --}}
                                        <a href="{{ route('teacher.patients.test', $patient) }}" class="btn-action"
                                            title="Gestionar test">
                                            <i data-lucide="clipboard-list"></i>
                                        </a>

                                        {{-- Publicar (solo si está en borrador) --}}
                                        @if(!$patient->is_published)
                                            <form action="{{ route('teacher.patients.publish', $patient) }}" method="POST"
                                                style="display: inline;">
                                                @csrf
                                                <button type="submit" class="btn-action btn-action-success"
                                                    title="Publicar paciente">
                                                    <i data-lucide="send"></i>
                                                </button>
                                            </form>
                                        @endif

                                        {{-- Eliminar --}}
                                        <form action="{{ route('teacher.patients.destroy', [$patient, 'dashboard']) }}"
                                            method="POST" style="display: inline;"
                                            onsubmit="return confirm('¿Eliminar este paciente? Esta acción no se puede deshacer.')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn-action btn-action-danger"
                                                title="Eliminar paciente">
                                                <i data-lucide="trash-2"></i>
                                            </button>
                                        </form>

                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Footer de la tarjeta con enlace a todos los pacientes --}}
            @if($totalPatients > 6)
                <div class="card-footer">
                    <a href="{{ route('teacher.patients.index') }}" class="btn btn-ghost btn-sm">
                        Ver los {{ $totalPatients }} pacientes
                        <i data-lucide="arrow-right"></i>
                    </a>
                </div>
            @endif

        @endif

    </div>

    <div class="card">
        <div class="card-header">
            <div class="card-header-title">
                <i data-lucide="book-open"></i>
                Asignaturas recientes
            </div>
            @if($totalSubjects > 0)
                <a href="{{ route('teacher.subjects.index') }}" class="btn btn-ghost btn-sm">
                    Ver todas
                    <i data-lucide="arrow-right"></i>
                </a>
            @endif
        </div>

        @if($recentSubjects->isEmpty())
            <div class="empty-state">
                <div class="empty-state-icon">
                    <i data-lucide="book-x"></i>
                </div>
                <div class="empty-state-title">Aún no tienes asignaturas</div>
                <div class="empty-state-text">
                    Crea tu primera asignatura para empezar a gestionar tus alumnos y pacientes.
                </div>
                <a href="{{ route('teacher.subjects.create') }}" class="btn btn-primary mt-md">
                    <i data-lucide="plus"></i>
                    Crear primera asignatura
                </a>
            </div>
        @else
            <div class="table-wrapper" style="border: none; border-radius: 0;">
                <table>
                    <thead>
                        <tr>
                            <th>Asignatura</th>
                            <th>Código</th>
                            <th>Institución</th>
                            <th>Alumnos</th>
                            <th>Pacientes</th>
                            <th class="actions">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($recentSubjects as $subject)
                            <tr>
                                <td>
                                    <div class="patient-name">{{ $subject->name }}</div>
                                </td>
                                <td>
                                    <span class="text-muted text-sm">{{ $subject->code }}</span>
                                </td>
                                <td>
                                    <span class="text-muted text-sm">{{ $subject->institution }}</span>
                                </td>
                                <td>
                                    <span class="badge badge-secondary">
                                        {{ $subject->students->count() }} alumnos
                                    </span>
                                </td>
                                <td>
                                    <span class="badge badge-primary">
                                        {{ $subject->patients->count() }} pacientes
                                    </span>
                                </td>
                                <td class="actions">
                                    <div class="row-actions">
                                        <a href="{{ route('teacher.subjects.show', $subject) }}" class="btn-action"
                                            title="Ver asignatura">
                                            <i data-lucide="eye"></i>
                                        </a>
                                        <a href="{{ route('teacher.subjects.edit', $subject) }}" class="btn-action"
                                            title="Editar asignatura">
                                            <i data-lucide="pencil"></i>
                                        </a>

                                        <form action="{{ route('teacher.subjects.destroy', $subject) }}" method="POST"
                                            style="display: inline;"
                                            onsubmit="return confirm('¿Eliminar esta asignatura? Se eliminarán también todos sus pacientes, alumnos y simulaciones. Esta acción no se puede deshacer.')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn-action btn-action-danger"
                                                title="Eliminar asignatura">
                                                <i data-lucide="trash-2"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if($totalSubjects > 4)
                <div class="card-footer">
                    <a href="{{ route('teacher.subjects.index') }}" class="btn btn-ghost btn-sm">
                        Ver las {{ $totalSubjects }} asignaturas
                        <i data-lucide="arrow-right"></i>
                    </a>
                </div>
            @endif
        @endif
    </div>




    <div class="card mt-lg">
        <div class="card-header">
            <div class="card-header-title">
                <i data-lucide="activity"></i>
                Actividad reciente
            </div>
            @if($recentActivity->isNotEmpty())
                <a href="{{ route('teacher.consultations.index') }}" class="btn btn-ghost btn-sm">
                    Ver todas
                    <i data-lucide="arrow-right"></i>
                </a>
            @endif
        </div>

        @if($recentActivity->isEmpty())
            <div class="empty-state">
                <div class="empty-state-icon"><i data-lucide="clock"></i></div>
                <div class="empty-state-title">Sin actividad aún</div>
                <div class="empty-state-text">Aquí aparecerán las últimas simulaciones de tus alumnos.</div>
            </div>
        @else
            <div class="table-wrapper" style="border: none; border-radius: 0;">
                <table>
                    <thead>
                        <tr>
                            <th>Alumno</th>
                            <th>Paciente</th>
                            <th>Estado</th>
                            <th>Fecha</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($recentActivity as $attempt)
                            <tr>
                                <td>
                                    <div class="patient-name">{{ $attempt->user?->full_name ?? '—' }}</div>
                                </td>
                                <td>
                                    <span class="text-sm text-muted">{{ $attempt->patient?->case_title ?? '—' }}</span>
                                </td>
                                <td>
                                    @if($attempt->final_score !== null)
                                        <span class="badge badge-success">Completada</span>
                                    @elseif($attempt->submitted_at)
                                        <span class="badge badge-warning">Pendiente corrección</span>
                                    @elseif($attempt->interview_transcript)
                                        <span class="badge badge-primary">Test pendiente</span>
                                    @else
                                        <span class="badge badge-secondary">En curso</span>
                                    @endif
                                </td>
                                <td class="text-muted text-sm">{{ $attempt->created_at->diffForHumans() }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>


    <div class="card mt-lg">
        <div class="card-header">
            <div class="card-header-title">
                <i data-lucide="clipboard-check"></i>
                Tests pendientes de corrección
            </div>
            @if($pendingGrading->isNotEmpty())
                <a href="{{ route('teacher.results.index') }}" class="btn btn-ghost btn-sm">
                    Ver todos
                    <i data-lucide="arrow-right"></i>
                </a>
            @endif
        </div>

        @if($pendingGrading->isEmpty())
            <div class="empty-state">
                <div class="empty-state-icon"><i data-lucide="check-circle"></i></div>
                <div class="empty-state-title">Todo al día</div>
                <div class="empty-state-text">No hay tests pendientes de corrección manual.</div>
            </div>
        @else
            <div class="table-wrapper" style="border: none; border-radius: 0;">
                <table>
                    <thead>
                        <tr>
                            <th>Alumno</th>
                            <th>Paciente</th>
                            <th>Enviado</th>
                            <th class="actions">Acción</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($pendingGrading as $attempt)
                            <tr>
                                <td>
                                    <div class="patient-name">{{ $attempt->user?->full_name ?? '—' }}</div>
                                </td>
                                <td>
                                    <span class="text-sm text-muted">{{ $attempt->patient?->case_title ?? '—' }}</span>
                                </td>
                                <td class="text-muted text-sm">{{ $attempt->submitted_at->diffForHumans() }}</td>
                                <td class="actions">
                                    <a href="{{ route('teacher.results.show', $attempt) }}" class="btn-action" title="Corregir">
                                        <i data-lucide="pencil"></i>
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>



    </x-app-layout>