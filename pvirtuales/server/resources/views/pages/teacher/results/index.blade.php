<x-layouts.app>

    <x-slot name="title">Resultados de Tests</x-slot>

    <x-slot name="styles">
        <link href="{{ asset('css/dashboard.css') }}" rel="stylesheet">
    </x-slot>

    <x-slot name="topbar">
        <div class="topbar">
            <div class="topbar-left">
                <div class="topbar-title">Resultados de Tests</div>
                <div class="topbar-subtitle">Tests enviados por tus alumnos</div>
            </div>
        </div>
    </x-slot>

    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-card-icon primary"><i data-lucide="clipboard-list"></i></div>
            <div class="stat-card-info">
                <div class="stat-card-value">{{ $totalSubmitted }}</div>
                <div class="stat-card-label">Tests enviados</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-card-icon warning"><i data-lucide="pencil"></i></div>
            <div class="stat-card-info">
                <div class="stat-card-value">{{ $pendingGrading }}</div>
                <div class="stat-card-label">Pendientes corrección</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-card-icon success"><i data-lucide="check-circle"></i></div>
            <div class="stat-card-info">
                <div class="stat-card-value">{{ $gradedCount }}</div>
                <div class="stat-card-label">Corregidos</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-card-icon secondary"><i data-lucide="bar-chart-2"></i></div>
            <div class="stat-card-info">
                <div class="stat-card-value">{{ $avgGrade ? number_format($avgGrade, 2) : '—' }}</div>
                <div class="stat-card-label">Nota media</div>
            </div>
        </div>
    </div>

    <div class="card mt-lg">
        <div class="card-header">
            <div class="card-header-title">
                <i data-lucide="clipboard-check"></i>
                Todos los resultados
            </div>
        </div>

        @if($attempts->isEmpty())
            <div class="empty-state">
                <div class="empty-state-icon"><i data-lucide="clipboard-x"></i></div>
                <div class="empty-state-title">Sin tests enviados aún</div>
                <div class="empty-state-text">Cuando tus alumnos envíen el cuestionario tras la consulta aparecerán aquí.</div>
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
                            <th>Enviado</th>
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
                                        <span class="badge badge-success">Corregido</span>
                                    @else
                                        <span class="badge badge-warning">Pdte. corrección</span>
                                    @endif
                                </td>
                                <td>
                                    @if($attempt->final_score !== null)
                                        @php $score = (float) $attempt->final_score; @endphp
                                        <span class="badge {{ $score >= 5 ? 'badge-success' : 'badge-danger' }}">
                                            {{ number_format($score, 2) }} / 10
                                        </span>
                                    @else
                                        <span class="text-muted text-sm">Pendiente</span>
                                    @endif
                                </td>
                                <td class="text-muted text-sm">{{ $attempt->submitted_at->diffForHumans() }}</td>
                                <td class="actions">
                                    <a href="{{ route('teacher.results.show', $attempt) }}"
                                       class="btn-action" title="{{ $attempt->final_score !== null ? 'Ver resultado' : 'Corregir' }}">
                                        <i data-lucide="{{ $attempt->final_score !== null ? 'eye' : 'pencil' }}"></i>
                                    </a>
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
