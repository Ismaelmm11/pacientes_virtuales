<x-layouts.app>

    <x-slot name="title">Mis Asignaturas</x-slot>

    <x-slot name="styles">
        <link href="{{ asset('css/dashboard.css') }}" rel="stylesheet">
        <link href="{{ asset('css/modal.css') }}" rel="stylesheet">
    </x-slot>

    <x-slot name="topbar">
        <div class="topbar">
            <div class="topbar-left">
                <div class="topbar-title">Mis Asignaturas</div>
                <div class="topbar-subtitle">Asignaturas en las que estás matriculado</div>
            </div>
        </div>
    </x-slot>

    {{-- Stats --}}
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-card-icon primary"><i data-lucide="book-open"></i></div>
            <div class="stat-card-info">
                <div class="stat-card-value">{{ $subjects->count() }}</div>
                <div class="stat-card-label">Asignaturas</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-card-icon secondary"><i data-lucide="user-round"></i></div>
            <div class="stat-card-info">
                <div class="stat-card-value">{{ $totalPatients }}</div>
                <div class="stat-card-label">Pacientes disponibles</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-card-icon success"><i data-lucide="clipboard-check"></i></div>
            <div class="stat-card-info">
                <div class="stat-card-value">{{ $totalCompleted }}</div>
                <div class="stat-card-label">Tests completados</div>
            </div>
        </div>
    </div>

    @if($subjects->isEmpty())
        <div class="card mt-lg">
            <div class="empty-state">
                <div class="empty-state-icon"><i data-lucide="book-x"></i></div>
                <div class="empty-state-title">No estás matriculado en ninguna asignatura</div>
                <div class="empty-state-text">Pide a tu profesor que te añada a su asignatura.</div>
            </div>
        </div>
    @else
        @foreach($subjects as $subject)
            <div class="card mt-lg">
                <div class="card-header">
                    <div class="card-header-title">
                        <i data-lucide="book-open"></i>
                        {{ $subject->name }}
                        @if($subject->code)
                            <span class="badge badge-neutral" style="margin-left: 8px;">{{ $subject->code }}</span>
                        @endif
                    </div>
                    @if($subject->institution)
                        <div class="card-header-actions">
                            <span class="text-muted text-sm">{{ $subject->institution }}</span>
                        </div>
                    @endif
                </div>

                @if($subject->patients_list->isEmpty())
                    <div class="empty-state" style="padding: 24px;">
                        <div class="empty-state-text">No hay pacientes publicados en esta asignatura.</div>
                    </div>
                @else
                    <div class="table-wrapper" style="border: none; border-radius: 0;">
                        <table>
                            <thead>
                                <tr>
                                    <th>Paciente</th>
                                    <th>Modo</th>
                                    <th>Intentos usados</th>
                                    <th>Tests completados</th>
                                    <th class="actions">Acción</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($subject->patients_list as $patient)
                                    @php
                                        $isUnlimited = $patient->max_attempts === -1;
                                        $canSimulate = $isUnlimited || $patient->attempts_used < $patient->max_attempts;
                                        $simUrl = route('simulation.start', ['aiModel' => 'claude', 'patientId' => $patient->id]);
                                    @endphp
                                    <tr>
                                        <td>
                                            <div class="patient-name">{{ $patient->case_title }}</div>
                                            @if($patient->patient_description)
                                                <div class="patient-desc">{{ $patient->patient_description }}</div>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge {{ $patient->mode === 'basic' ? 'badge-secondary' : 'badge-primary' }}">
                                                {{ $patient->mode === 'basic' ? 'Básico' : 'Avanzado' }}
                                            </span>
                                        </td>
                                        <td>
                                            @if($isUnlimited)
                                                <span class="badge badge-neutral">∞ ilimitados</span>
                                            @else
                                                <span class="badge {{ $canSimulate ? 'badge-secondary' : 'badge-danger' }}">
                                                    {{ $patient->attempts_used }} / {{ $patient->max_attempts }}
                                                </span>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge {{ $total > 0 ? 'badge-success' : 'badge-neutral' }}">
                                                {{ $total }}
                                            </span>
                                        </td>
                                        <td class="actions">
                                            @if($canSimulate)
                                                <a href="{{ $simUrl }}" class="btn btn-primary btn-sm">
                                                    <i data-lucide="play"></i>
                                                    Simular
                                                </a>
                                            @else
                                                <span class="badge badge-neutral">Sin intentos</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        @endforeach
    @endif

</x-layouts.app>
