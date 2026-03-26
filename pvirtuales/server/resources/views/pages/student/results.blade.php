<x-layouts.app>

    <x-slot name="title">Mis Resultados</x-slot>

    <x-slot name="styles">
        <link href="{{ asset('css/dashboard.css') }}" rel="stylesheet">
    </x-slot>

    <x-slot name="topbar">
        <div class="topbar">
            <div class="topbar-left">
                <div class="topbar-title">Mis Resultados</div>
                <div class="topbar-subtitle">Tests completados y notas obtenidas</div>
            </div>
        </div>
    </x-slot>

    {{-- Stats --}}
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-card-icon primary"><i data-lucide="clipboard-check"></i></div>
            <div class="stat-card-info">
                <div class="stat-card-value">{{ $total }}</div>
                <div class="stat-card-label">Tests completados</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-card-icon secondary"><i data-lucide="trending-up"></i></div>
            <div class="stat-card-info">
                <div class="stat-card-value">{{ $avgGrade !== null ? number_format($avgGrade, 1) : '—' }}</div>
                <div class="stat-card-label">Nota media</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-card-icon success"><i data-lucide="award"></i></div>
            <div class="stat-card-info">
                <div class="stat-card-value">{{ $bestGrade !== null ? number_format($bestGrade, 1) : '—' }}</div>
                <div class="stat-card-label">Mejor nota</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-card-icon warning"><i data-lucide="check-circle"></i></div>
            <div class="stat-card-info">
                <div class="stat-card-value">{{ $total > 0 ? $passCount . '/' . $total : '—' }}</div>
                <div class="stat-card-label">Aprobados (≥50)</div>
            </div>
        </div>
    </div>

    <div class="card mt-lg">
        <div class="card-header">
            <div class="card-header-title">
                <i data-lucide="award"></i>
                Historial de resultados
            </div>
        </div>

        @if($completedTests->isEmpty())
            <div class="empty-state">
                <div class="empty-state-icon"><i data-lucide="clipboard-x"></i></div>
                <div class="empty-state-title">Sin resultados todavía</div>
                <div class="empty-state-text">Aquí aparecerán tus notas cuando completes los tests de evaluación.</div>
            </div>
        @else
            <div class="table-wrapper" style="border: none; border-radius: 0;">
                <table>
                    <thead>
                        <tr>
                            <th>Paciente</th>
                            <th>Asignatura</th>
                            <th>Nota</th>
                            <th>Calificación</th>
                            <th>Estado</th>
                            <th>Fecha</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($completedTests as $attempt)
                            @php $score = (float) $attempt->final_score; @endphp
                            <tr>
                                <td>
                                    <div class="patient-name">{{ $attempt->patient?->case_title ?? '—' }}</div>
                                </td>
                                <td>
                                    <span class="text-muted text-sm">{{ $attempt->patient?->subject?->name ?? '—' }}</span>
                                </td>
                                <td>
                                    <span
                                        class="badge {{ $score >= 70 ? 'badge-success' : ($score >= 50 ? 'badge-warning' : 'badge-danger') }}"
                                        style="font-size: 0.9rem; font-weight: 700;">
                                        {{ number_format($score, 1) }}
                                    </span>
                                </td>
                                <td>
                                    @if($attempt->final_score !== null)
                                        @php $score = (float) $attempt->final_score; @endphp
                                        <span
                                            class="badge {{ $score >= 70 ? 'badge-success' : ($score >= 50 ? 'badge-warning' : 'badge-danger') }}"
                                            style="font-size: 0.9rem; font-weight: 700;">
                                            {{ number_format($score, 1) }}
                                        </span>
                                    @else
                                        <span class="badge badge-neutral">Pendiente</span>
                                    @endif
                                </td>
                                <td>
                                    @if($attempt->final_score !== null)
                                        @php $score = (float) $attempt->final_score; @endphp
                                        @if($score >= 90) <span class="text-sm"
                                            style="color:#2BA88A;font-weight:600">Sobresaliente</span>
                                        @elseif($score >= 70) <span class="text-sm" style="color:#2BA88A">Notable</span>
                                        @elseif($score >= 50) <span class="text-sm" style="color:#C07D0A">Aprobado</span>
                                        @else <span class="text-sm" style="color:var(--color-danger)">Suspenso</span>
                                        @endif
                                    @else
                                        <span class="text-muted text-sm">En revisión</span>
                                    @endif
                                </td>

                                <td class="text-muted text-sm">
                                    {{ $attempt->created_at->format('d/m/Y H:i') }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

</x-layouts.app>