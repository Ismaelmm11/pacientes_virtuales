{{--
|--------------------------------------------------------------------------
| Dashboard del Administrador
|--------------------------------------------------------------------------
|
| Vista principal del admin. Visión global de la plataforma.
|
| DATOS RECIBIDOS:
| $totalTeachers, $totalStudents, $totalSubjects, $totalPatients
| $totalSimulations, $pendingGrading → int
| $recentSimulations → Collection<TestAttempt>
| $topTeachers       → Collection<User> (con patients_count)
| $topPatients       → Collection<Patient> (con test_attempts_count)
|
--}}

<x-layouts.app>

    <x-slot name="title">Admin — Dashboard</x-slot>

    <x-slot name="styles">
        <link href="{{ asset('css/dashboard.css') }}" rel="stylesheet">
        <link href="{{ asset('css/admin.css') }}" rel="stylesheet">
    </x-slot>

    <x-slot name="topbar">
        <div class="topbar">
            <div class="topbar-left">
                <div class="topbar-title">Panel de Administración</div>
                <div class="topbar-subtitle">Visión global de la plataforma · {{ now()->isoFormat('D [de] MMMM [de] YYYY') }}</div>
            </div>
        </div>
    </x-slot>

    {{-- ---- 1. STATS GLOBALES ---- --}}
    <div class="stats-grid">

        <div class="stat-card">
            <div class="stat-card-icon primary">
                <i data-lucide="graduation-cap"></i>
            </div>
            <div class="stat-card-info">
                <div class="stat-card-value">{{ $totalTeachers }}</div>
                <div class="stat-card-label">Profesores</div>
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
                <i data-lucide="book-open"></i>
            </div>
            <div class="stat-card-info">
                <div class="stat-card-value">{{ $totalSubjects }}</div>
                <div class="stat-card-label">Asignaturas</div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-card-icon primary">
                <i data-lucide="user-round"></i>
            </div>
            <div class="stat-card-info">
                <div class="stat-card-value">{{ $totalPatients }}</div>
                <div class="stat-card-label">Pacientes virtuales</div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-card-icon secondary">
                <i data-lucide="message-square"></i>
            </div>
            <div class="stat-card-info">
                <div class="stat-card-value">{{ $totalSimulations }}</div>
                <div class="stat-card-label">Simulaciones totales</div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-card-icon danger">
                <i data-lucide="clipboard-x"></i>
            </div>
            <div class="stat-card-info">
                <div class="stat-card-value">{{ $pendingGrading }}</div>
                <div class="stat-card-label">Tests sin corregir</div>
            </div>
        </div>

    </div>

    {{-- ---- 2. ACTIVIDAD RECIENTE ---- --}}
    <div class="card mt-lg">

        <div class="card-header">
            <div class="card-header-title">
                <i data-lucide="activity"></i>
                Actividad reciente
            </div>
        </div>

        @if($recentSimulations->isEmpty())
            <div class="empty-state">
                <div class="empty-state-icon"><i data-lucide="clock"></i></div>
                <div class="empty-state-title">Sin actividad todavía</div>
                <div class="empty-state-text">Aquí aparecerán las últimas simulaciones de todos los alumnos.</div>
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
                        @foreach($recentSimulations as $attempt)
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

    {{-- ---- 3. TOP PROFESORES + TOP PACIENTES ---- --}}
    <div class="admin-two-col">

        {{-- Profesores más activos --}}
        <div class="card">
            <div class="card-header">
                <div class="card-header-title">
                    <i data-lucide="trophy"></i>
                    Profesores más activos
                </div>
                @if(Route::has('admin.users.index'))
                    <a href="{{ route('admin.users.index') }}" class="btn btn-ghost btn-sm">
                        Ver todos
                        <i data-lucide="arrow-right"></i>
                    </a>
                @endif
            </div>

            @if($topTeachers->isEmpty())
                <div class="empty-state">
                    <div class="empty-state-icon"><i data-lucide="user-x"></i></div>
                    <div class="empty-state-title">Sin profesores aún</div>
                </div>
            @else
                <div class="table-wrapper" style="border: none; border-radius: 0;">
                    <table>
                        <thead>
                            <tr>
                                <th>Profesor</th>
                                <th>Pacientes</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($topTeachers as $i => $teacher)
                                <tr>
                                    <td>
                                        <div class="admin-rank-row">
                                            <span class="admin-rank-num">{{ $i + 1 }}</span>
                                            <div class="patient-name">{{ $teacher->full_name }}</div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge badge-primary">{{ $teacher->patients_count }}</span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

        {{-- Pacientes más simulados --}}
        <div class="card">
            <div class="card-header">
                <div class="card-header-title">
                    <i data-lucide="bar-chart-2"></i>
                    Pacientes más simulados
                </div>
                @if(Route::has('admin.patients.index'))
                    <a href="{{ route('admin.patients.index') }}" class="btn btn-ghost btn-sm">
                        Ver todos
                        <i data-lucide="arrow-right"></i>
                    </a>
                @endif
            </div>

            @if($topPatients->isEmpty())
                <div class="empty-state">
                    <div class="empty-state-icon"><i data-lucide="user-round-x"></i></div>
                    <div class="empty-state-title">Sin simulaciones aún</div>
                </div>
            @else
                <div class="table-wrapper" style="border: none; border-radius: 0;">
                    <table>
                        <thead>
                            <tr>
                                <th>Paciente</th>
                                <th>Simulaciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($topPatients as $i => $patient)
                                <tr>
                                    <td>
                                        <div class="admin-rank-row">
                                            <span class="admin-rank-num">{{ $i + 1 }}</span>
                                            <div class="patient-name">{{ $patient->case_title }}</div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge badge-secondary">{{ $patient->test_attempts_count }}</span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

    </div>

</x-layouts.app>
