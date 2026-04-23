<x-layouts.app>

    <x-slot name="title">Mis Consultas</x-slot>

    <x-slot name="styles">
        <link href="{{ asset('css/dashboard.css') }}" rel="stylesheet">
    </x-slot>

    <x-slot name="topbar">
        <div class="topbar">
            <div class="topbar-left">
                <div class="topbar-title">Mis Consultas</div>
                <div class="topbar-subtitle">Historial completo de simulaciones</div>
            </div>
        </div>
    </x-slot>

    {{-- Stats --}}
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-card-icon primary"><i data-lucide="message-square"></i></div>
            <div class="stat-card-info">
                <div class="stat-card-value">{{ $totalCount }}</div>
                <div class="stat-card-label">Total consultas</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-card-icon warning"><i data-lucide="clock"></i></div>
            <div class="stat-card-info">
                <div class="stat-card-value">{{ $pendingCount }}</div>
                <div class="stat-card-label">Tests pendientes</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-card-icon success"><i data-lucide="check-circle"></i></div>
            <div class="stat-card-info">
                <div class="stat-card-value">{{ $completedCount }}</div>
                <div class="stat-card-label">Completadas</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-card-icon warning"><i data-lucide="hourglass"></i></div>
            <div class="stat-card-info">
                <div class="stat-card-value">{{ $gradingCount }}</div>
                <div class="stat-card-label">Pendientes corrección</div>
            </div>
        </div>
    </div>

    <div class="card mt-lg">
        <div class="card-header">
            <div class="card-header-title">
                <i data-lucide="message-square"></i>
                Todas las consultas
            </div>
        </div>

        @if($attempts->isEmpty())
            <div class="empty-state">
                <div class="empty-state-icon"><i data-lucide="message-square-off"></i></div>
                <div class="empty-state-title">Sin consultas todavía</div>
                <div class="empty-state-text">Aquí aparecerá tu historial cuando realices tu primera simulación.</div>
            </div>
        @else
            <div class="table-wrapper" style="border: none; border-radius: 0;">
                <table>
                    <thead>
                        <tr>
                            <th>Paciente</th>
                            <th>Asignatura</th>
                            <th>Fecha</th>
                            <th>Estado</th>
                            <th>Nota</th>
                            <th class="actions">Acción</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($attempts as $attempt)
                            @php
                                $hasTranscript = $attempt->interview_transcript !== null;
                                $isSubmitted = $attempt->submitted_at !== null;
                                $isGraded = $attempt->final_score !== null;
                                $isPending = !$isSubmitted && $hasTranscript;
                                $isInProgress = !$isSubmitted && !$hasTranscript;
                                $awaitingGrade = $isSubmitted && !$isGraded;
                            @endphp
                            <tr>
                                <td>
                                    <div class="patient-name">{{ $attempt->patient?->case_title ?? '—' }}</div>
                                </td>
                                <td>
                                    <span class="text-muted text-sm">{{ $attempt->patient?->subject?->name ?? '—' }}</span>
                                </td>
                                <td class="text-muted text-sm">
                                    {{ $attempt->created_at->format('d/m/Y H:i') }}
                                </td>
                                <td>
                                    @if($isGraded)
                                        <span class="badge badge-success">
                                            <i data-lucide="check"></i> Completada
                                        </span>
                                    @elseif($awaitingGrade)
                                        <span class="badge badge-secondary">
                                            <i data-lucide="hourglass"></i> Pendiente corrección
                                        </span>
                                    @elseif($isPending)
                                        <span class="badge badge-warning">
                                            <i data-lucide="clock"></i> Test pendiente
                                        </span>
                                    @else
                                        <span class="badge badge-neutral">
                                            <i data-lucide="loader"></i> En curso
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    @if($attempt->submitted_at)
                                        @if($attempt->patient->results_published && $attempt->final_score !== null)
                                            {{-- Resultados publicados: mostrar nota --}}
                                            <span>{{ number_format($attempt->final_score, 1) }} / 10</span>
                                        @elseif($attempt->submitted_at)
                                            {{-- Test enviado pero resultados no publicados aún --}}
                                            <span class="text-muted">Pendiente de publicación</span>
                                        @endif
                                    @endif
                                </td>
                                <td class="actions">
                                    @if($isPending && $attempt->patient?->hasTest())
                                        <a href="{{ route('patients.test.take', $attempt->patient) }}"
                                            class="btn btn-primary btn-sm">
                                            <i data-lucide="clipboard-check"></i>
                                            Completar test
                                        </a>
                                    @else
                                        <span class="text-muted text-sm">—</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

</x-layouts.app>