{{--
|--------------------------------------------------------------------------
| Asignaturas del Profesor — Index
|--------------------------------------------------------------------------
|
| DATOS RECIBIDOS DEL CONTROLADOR:
|   $ownedSubjects          → Collection de Subject (propietario)
|   $collaboratingSubjects  → Collection de Subject (colaborador)
|
--}}

<x-layouts.app>

    <x-slot name="title">Asignaturas</x-slot>

    <x-slot name="styles">
        <link href="{{ asset('css/dashboard.css') }}" rel="stylesheet">
    </x-slot>

    <x-slot name="topbar">
        <div class="topbar">
            <div class="topbar-left">
                <div class="topbar-title">Mis Asignaturas</div>
                <div class="topbar-subtitle">Gestiona tus asignaturas y alumnos</div>
            </div>
            <div class="topbar-right">
                <a href="{{ route('teacher.subjects.create') }}" class="btn btn-primary btn-sm">
                    <i data-lucide="plus"></i>
                    Nueva Asignatura
                </a>
            </div>
        </div>
    </x-slot>

    {{-- ================================================================
         ASIGNATURAS PROPIAS
         ================================================================ --}}

    <div class="card">
        <div class="card-header">
            <div class="card-header-title">
                <i data-lucide="book-open"></i>
                Mis asignaturas
            </div>
        </div>

        @if($ownedSubjects->isEmpty())
            <div class="empty-state">
                <div class="empty-state-icon">
                    <i data-lucide="book-x"></i>
                </div>
                <div class="empty-state-title">Aún no tienes asignaturas</div>
                <div class="empty-state-text">
                    Crea tu primera asignatura para empezar a gestionar tus alumnos y pacientes virtuales.
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
                        @foreach($ownedSubjects as $subject)
                            <tr>
                                <td>
                                    <div class="subject-name">{{ $subject->name }}</div>
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

                                        {{-- Ver detalle --}}
                                        <a href="{{ route('teacher.subjects.show', $subject) }}"
                                           class="btn-action"
                                           title="Ver asignatura">
                                            <i data-lucide="eye"></i>
                                        </a>

                                        {{-- Editar --}}
                                        <a href="{{ route('teacher.subjects.edit', $subject) }}"
                                           class="btn-action"
                                           title="Editar asignatura">
                                            <i data-lucide="pencil"></i>
                                        </a>

                                        {{-- Eliminar --}}
                                        <form action="{{ route('teacher.subjects.destroy', $subject) }}"
                                              method="POST"
                                              style="display: inline;"
                                              onsubmit="return confirm('¿Eliminar esta asignatura? Se eliminarán también todos sus pacientes, alumnos y simulaciones. Esta acción no se puede deshacer.')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                    class="btn-action btn-action-danger"
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
        @endif
    </div>

    {{-- ================================================================
         ASIGNATURAS DONDE ES COLABORADOR
         ================================================================ --}}

    @if($collaboratingSubjects->isNotEmpty())
        <div class="card mt-lg">
            <div class="card-header">
                <div class="card-header-title">
                    <i data-lucide="users"></i>
                    Asignaturas donde colaboro
                </div>
            </div>

            <div class="table-wrapper" style="border: none; border-radius: 0;">
                <table>
                    <thead>
                        <tr>
                            <th>Asignatura</th>
                            <th>Código</th>
                            <th>Institución</th>
                            <th>Propietario</th>
                            <th>Alumnos</th>
                            <th>Pacientes</th>
                            <th class="actions">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($collaboratingSubjects as $subject)
                            <tr>
                                <td>
                                    <div class="subject-name">{{ $subject->name }}</div>
                                </td>
                                <td>
                                    <span class="text-muted text-sm">{{ $subject->code }}</span>
                                </td>
                                <td>
                                    <span class="text-muted text-sm">{{ $subject->institution }}</span>
                                </td>
                                <td>
                                    <span class="text-muted text-sm">{{ $subject->creator->full_name }}</span>
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
                                        {{-- Solo puede ver, no editar ni eliminar --}}
                                        <a href="{{ route('teacher.subjects.show', $subject) }}"
                                           class="btn-action"
                                           title="Ver asignatura">
                                            <i data-lucide="eye"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

</x-layouts.app>