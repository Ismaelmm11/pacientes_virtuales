{{--
|--------------------------------------------------------------------------
| Detalle de Asignatura
|--------------------------------------------------------------------------
|
| DATOS RECIBIDOS DEL CONTROLADOR:
| $subject → Subject con relaciones: students, collaborators, patients, creator
|
--}}

<x-layouts.app>

    <x-slot name="title">{{ $subject->name }}</x-slot>

    <x-slot name="styles">
        <link href="{{ asset('css/dashboard.css') }}" rel="stylesheet">
    </x-slot>

    <x-slot name="topbar">
        <div class="topbar">
            <div class="topbar-left">
                <div class="topbar-title">{{ $subject->name }}</div>
                <div class="topbar-subtitle">{{ $subject->institution }} · {{ $subject->code }}</div>
            </div>
            <div class="topbar-right">
                @if($subject->created_by_user_id === Auth::id())
                    <a href="{{ route('teacher.subjects.edit', $subject) }}" class="btn btn-ghost btn-sm">
                        <i data-lucide="pencil"></i>
                        Editar
                    </a>
                @endif
                <a href="{{ route('teacher.subjects.index') }}" class="btn btn-ghost btn-sm">
                    <i data-lucide="arrow-left"></i>
                    Volver
                </a>
            </div>
        </div>
    </x-slot>

    {{-- ================================================================
    PACIENTES
    ================================================================ --}}

    <div class="card">
        <div class="card-header">
            <div class="card-header-title">
                <i data-lucide="user-round"></i>
                Pacientes virtuales
            </div>
            <a href="{{ route('teacher.patients.create') }}" class="btn btn-primary btn-sm">
                <i data-lucide="plus"></i>
                Nuevo Paciente
            </a>
        </div>

        @if($subject->patients->isEmpty())
            <div class="empty-state">
                <div class="empty-state-icon">
                    <i data-lucide="user-round-x"></i>
                </div>
                <div class="empty-state-title">Aún no hay pacientes</div>
                <div class="empty-state-text">
                    Crea el primer paciente virtual para esta asignatura.
                </div>
            </div>
        @else
            <div class="table-wrapper" style="border: none; border-radius: 0;">
                <table>
                    <thead>
                        <tr>
                            <th>Paciente</th>
                            <th>Modo</th>
                            <th>Estado</th>
                            <th>Creado</th>
                            <th class="actions">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($subject->patients as $patient)
                            <tr>
                                <td>
                                    <div class="patient-name">{{ $patient->case_title }}</div>
                                    @if($patient->patient_description)
                                        <div class="patient-desc">{{ $patient->patient_description }}</div>
                                    @endif
                                </td>
                                <td>
                                    @if($patient->mode === 'basic')
                                        <span class="badge badge-secondary">Básico</span>
                                    @else
                                        <span class="badge badge-primary">Avanzado</span>
                                    @endif
                                </td>
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
                                <td class="text-muted text-sm">
                                    {{ $patient->created_at->diffForHumans() }}
                                </td>
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
                                        <<form action="{{ route('teacher.patients.destroy', [$patient, 'show']) }}"
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
        @endif
    </div>

    {{-- ================================================================
    ALUMNOS
    ================================================================ --}}

    <div class="card mt-lg">
        <div class="card-header">
            <div class="card-header-title">
                <i data-lucide="users"></i>
                Alumnos inscritos
            </div>
        </div>

        {{-- Formulario para inscribir alumno --}}
        {{-- Formulario individual --}}
        <div class="card-body-form">
            <form action="{{ route('teacher.subjects.students.enroll', $subject) }}" method="POST" class="form-inline">
                @csrf
                <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                    placeholder="Email del alumno" required>
                <button type="submit" class="btn btn-primary btn-sm">
                    <i data-lucide="user-plus"></i>
                    Añadir alumno
                </button>
            </form>
            @error('email')
                <div class="form-error">{{ $message }}</div>
            @enderror
            @if(session('success'))
                <div class="form-success">{{ session('success') }}</div>
            @endif
            @if(session('error'))
                <div class="form-error">{{ session('error') }}</div>
            @endif
        </div>

        {{-- Formulario importación masiva --}}
        <div class="card-body-form" style="border-top: 1px solid var(--border);">
            <form action="{{ route('teacher.subjects.students.bulk-enroll', $subject) }}" method="POST"
                enctype="multipart/form-data" class="form-inline">
                @csrf
                <input type="file" name="file" accept=".csv" class="form-control" required>
                <button type="submit" class="btn btn-secondary btn-sm">
                    <i data-lucide="upload"></i>
                    Importar desde CSV
                </button>

            </form>
            @if(session('bulk_error'))
                <div class="form-error">{{ session('bulk_error') }}</div>
            @endif
        </div>

        {{-- Resultados de importación --}}
        @if(session('bulk_enrolled') || session('bulk_invited') || session('bulk_already_enrolled') || session('bulk_failed'))
            <div class="card-body-form">
                @if(session('bulk_enrolled'))
                    <div class="form-success">
                        <strong>Inscritos ({{ count(session('bulk_enrolled')) }}):</strong>
                        {{ implode(', ', session('bulk_enrolled')) }}
                    </div>
                @endif
                @if(session('bulk_invited'))
                    <div class="form-success">
                        <strong>Invitación enviada ({{ count(session('bulk_invited')) }}):</strong>
                        {{ implode(', ', session('bulk_invited')) }}
                    </div>
                @endif
                @if(session('bulk_already_enrolled'))
                    <div class="form-info">
                        <strong>Ya inscritos ({{ count(session('bulk_already_enrolled')) }}):</strong>
                        {{ implode(', ', session('bulk_already_enrolled')) }}
                    </div>
                @endif
                @if(session('bulk_failed'))
                    <div class="form-error">
                        <strong>No se pudo enviar el email, vuelve a intentarlo:</strong><br>
                        @foreach(session('bulk_failed') as $failedEmail)
                            {{ $failedEmail }}<br>
                        @endforeach
                    </div>
                @endif
            </div>
        @endif


        @if($subject->students->isEmpty())
            <div class="empty-state">
                <div class="empty-state-icon">
                    <i data-lucide="users"></i>
                </div>
                <div class="empty-state-title">Aún no hay alumnos inscritos</div>
                <div class="empty-state-text">
                    Introduce el email del alumno para inscribirlo en la asignatura.
                </div>
            </div>
        @else
            <div class="table-wrapper" style="border: none; border-radius: 0;">
                <table>
                    <thead>
                        <tr>
                            <th>Alumno</th>
                            <th>Email</th>
                            <th class="actions">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($subject->students as $student)
                            <tr>
                                <td>{{ $student->full_name }}</td>
                                <td class="text-muted text-sm">{{ $student->email }}</td>
                                <td class="actions">
                                    <div class="row-actions">
                                        <form action="{{ route('teacher.subjects.students.unenroll', [$subject, $student]) }}"
                                            method="POST" style="display: inline;"
                                            onsubmit="return confirm('¿Eliminar a {{ $student->full_name }} de la asignatura?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn-action btn-action-danger" title="Eliminar alumno">
                                                <i data-lucide="user-minus"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    {{-- ================================================================
    COLABORADORES (solo visible para el propietario)
    ================================================================ --}}

    @if($subject->created_by_user_id === Auth::id())
        <div class="card mt-lg">
            <div class="card-header">
                <div class="card-header-title">
                    <i data-lucide="user-check"></i>
                    Profesores colaboradores
                </div>
            </div>

            {{-- Formulario para invitar colaborador --}}
            <div class="card-body-form">
                <form action="{{ route('teacher.subjects.collaborators.invite', $subject) }}" method="POST"
                    class="form-inline">
                    @csrf
                    <input type="email" name="email" class="form-control" placeholder="Email del profesor" required>
                    <button type="submit" class="btn btn-primary btn-sm">
                        <i data-lucide="user-plus"></i>
                        Invitar colaborador
                    </button>
                </form>
            </div>

            @if($subject->collaborators->isEmpty())
                <div class="empty-state">
                    <div class="empty-state-icon">
                        <i data-lucide="user-check"></i>
                    </div>
                    <div class="empty-state-title">Aún no hay colaboradores</div>
                    <div class="empty-state-text">
                        Invita a otros profesores para que colaboren en esta asignatura.
                    </div>
                </div>
            @else
                <div class="table-wrapper" style="border: none; border-radius: 0;">
                    <table>
                        <thead>
                            <tr>
                                <th>Profesor</th>
                                <th>Email</th>
                                <th class="actions">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($subject->collaborators as $collaborator)
                                <tr>
                                    <td>{{ $collaborator->full_name }}</td>
                                    <td class="text-muted text-sm">{{ $collaborator->email }}</td>
                                    <td class="actions">
                                        <div class="row-actions">
                                            <form
                                                action="{{ route('teacher.subjects.collaborators.remove', [$subject, $collaborator]) }}"
                                                method="POST" style="display: inline;"
                                                onsubmit="return confirm('¿Eliminar a {{ $collaborator->full_name }} como colaborador?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn-action btn-action-danger"
                                                    title="Eliminar colaborador">
                                                    <i data-lucide="user-minus"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    @endif

</x-layouts.app>