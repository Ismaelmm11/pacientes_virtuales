<x-layouts.app>

    <x-slot name="title">Consultas — Seguimiento</x-slot>

    <x-slot name="styles">
        <link href="{{ asset('css/dashboard.css') }}" rel="stylesheet">
    </x-slot>

    <x-slot name="topbar">
        <div class="topbar">
            <div class="topbar-left">
                <div class="topbar-title">Consultas</div>
                <div class="topbar-subtitle">Seguimiento de simulaciones de tus alumnos</div>
            </div>
        </div>
    </x-slot>

    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-card-icon primary"><i data-lucide="message-square"></i></div>
            <div class="stat-card-info">
                <div class="stat-card-value">{{ $totalCount }}</div>
                <div class="stat-card-label">Total consultas</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-card-icon secondary"><i data-lucide="clock"></i></div>
            <div class="stat-card-info">
                <div class="stat-card-value">{{ $pendingCount }}</div>
                <div class="stat-card-label">Test pendiente</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-card-icon warning"><i data-lucide="pencil"></i></div>
            <div class="stat-card-info">
                <div class="stat-card-value">{{ $gradingCount }}</div>
                <div class="stat-card-label">Pendiente corrección</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-card-icon success"><i data-lucide="check-circle"></i></div>
            <div class="stat-card-info">
                <div class="stat-card-value">{{ $completedCount }}</div>
                <div class="stat-card-label">Completadas</div>
            </div>
        </div>
    </div>

    <div class="card mt-lg">
        <div class="card-header">
            <div class="card-header-title">
                <i data-lucide="list"></i>
                Todas las consultas
            </div>
        </div>

        @if($attempts->isEmpty())
            <div class="empty-state">
                <div class="empty-state-icon"><i data-lucide="message-square-x"></i></div>
                <div class="empty-state-title">Sin consultas aún</div>
                <div class="empty-state-text">Cuando tus alumnos realicen simulaciones aparecerán aquí.</div>
            </div>
        @else
            <div class="table-wrapper" style="border: none; border-radius: 0;">
                <table>
                    <thead>
                        <tr>
                            <th>Alumno</th>
                            <th>Paciente</th>
                            <th>Asignatura</th>
                            <th>Estado</th>
                            <th>Nota</th>
                            <th>Fecha</th>
                            <th class="actions">Acción</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($attempts as $attempt)
                            <tr>
                                <td>
                                    <div class="patient-name">{{ $attempt->user?->full_name ?? '—' }}</div>
                                    <div class="patient-desc">{{ $attempt->user?->email ?? '' }}</div>
                                </td>
                                <td>
                                    <span class="text-sm">{{ $attempt->patient?->case_title ?? '—' }}</span>
                                </td>
                                <td>
                                    <span class="text-sm text-muted">{{ $attempt->patient?->subject?->name ?? '—' }}</span>
                                </td>
                                <td>
                                    @if($attempt->final_score !== null)
                                        <span class="badge badge-success">Completada</span>
                                    @elseif($attempt->submitted_at)
                                        <span class="badge badge-warning">Pdte. corrección</span>
                                    @elseif($attempt->interview_transcript)
                                        <span class="badge badge-primary">Test pendiente</span>
                                    @else
                                        <span class="badge badge-secondary">En curso</span>
                                    @endif
                                </td>
                                <td>
                                    @if($attempt->final_score !== null)
                                        <span class="badge {{ $attempt->final_score >= 5 ? 'badge-success' : 'badge-danger' }}">
                                            {{ number_format($attempt->final_score, 2) }}
                                        </span>
                                    @else
                                        <span class="text-muted text-sm">—</span>
                                    @endif
                                </td>
                                <td class="text-muted text-sm">{{ $attempt->created_at->diffForHumans() }}</td>
                                <td class="actions">
                                    <div class="row-actions">
                                        @if($attempt->submitted_at)
                                            <a href="{{ route('teacher.results.show', $attempt) }}"
                                               class="btn-action" title="Ver resultado">
                                                <i data-lucide="eye"></i>
                                            </a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @if($attempts->hasPages())
                <div class="card-footer">
                    {{ $attempts->links() }}
                </div>
            @endif
        @endif
    </div>

</x-layouts.app>
