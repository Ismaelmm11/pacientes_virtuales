<x-layouts.app>

    <x-slot name="title">Resultados de Exámenes</x-slot>

    <x-slot name="styles">
        <link href="{{ asset('css/dashboard.css') }}" rel="stylesheet">
    </x-slot>

    <x-slot name="topbar">
        <div class="topbar">
            <div class="topbar-left">
                <div class="topbar-title">Resultados de Exámenes</div>
                <div class="topbar-subtitle">Pacientes marcados como examen y el estado de sus correcciones</div>
            </div>
        </div>
    </x-slot>

    <div class="card mt-lg">
        <div class="card-header">
            <div class="card-header-title">
                <i data-lucide="clipboard-check"></i>
                Pacientes de examen
            </div>
        </div>

        @if($examPatients->isEmpty())
            <div class="empty-state">
                <div class="empty-state-icon"><i data-lucide="clipboard-x"></i></div>
                <div class="empty-state-title">Sin pacientes de examen</div>
                <div class="empty-state-text">
                    Cuando marques un paciente como examen aparecerá aquí con el estado de sus correcciones.
                </div>
            </div>
        @else
            <div class="table-wrapper" style="border: none; border-radius: 0;">
                <table>
                    <thead>
                        <tr>
                            <th>Paciente</th>
                            <th>Asignatura</th>
                            <th>Consultas</th>
                            <th>Estado</th>
                            <th>Nota media</th>
                            <th class="actions">Acción</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($examPatients as $patient)
                            <tr>
                                {{-- Título del paciente --}}
                                <td>
                                    <div class="patient-name">{{ $patient->case_title }}</div>
                                    @if($patient->patient_description)
                                        <div class="patient-desc">{{ $patient->patient_description }}</div>
                                    @endif
                                </td>

                                {{-- Asignatura --}}
                                <td>
                                    <span class="text-sm text-muted">{{ $patient->subject?->name ?? '—' }}</span>
                                </td>

                                {{-- Consultas hechas / total posibles --}}
                                <td>
                                    @if($patient->total_possible === null)
                                        {{-- Intentos ilimitados: solo mostramos las hechas --}}
                                        <span class="text-sm">{{ $patient->submitted_count }}</span>
                                    @else
                                        <span class="text-sm">
                                            {{ $patient->submitted_count }} / {{ $patient->total_possible }}
                                        </span>
                                    @endif
                                </td>

                                {{-- Estado: publicado o pendiente de corrección --}}
                                <td>
                                    @if($patient->results_published)
                                        <span class="badge badge-success">Publicado</span>
                                    @elseif($patient->pending_grading > 0)
                                        <span class="badge badge-warning">Pdte. corrección</span>
                                    @elseif($patient->submitted_count > 0 && $patient->total_possible !== null && $patient->submitted_count >= $patient->total_possible)
                                        {{-- Todos los intentos realizados y todos corregidos --}}
                                        <span class="badge badge-secondary">Listo para publicar</span>
                                    @elseif($patient->submitted_count > 0)
                                        {{-- Todos los intentos realizados y todos corregidos --}}
                                        <span class="badge badge-secondary">Faltan
                                            {{ $patient->total_possible - $patient->submitted_count }} consultas</span>
                                    @else
                                        <span class="badge badge-secondary">Sin entregas</span>
                                    @endif

                                </td>

                                {{-- Nota media (solo si publicado) --}}
                                <td>
                                    @if($patient->avg_grade !== null)
                                        @php $avg = (float) $patient->avg_grade; @endphp
                                        <span class="badge {{ $avg >= 5 ? 'badge-success' : 'badge-danger' }}">
                                            {{ number_format($avg, 2) }} / 10
                                        </span>
                                    @else
                                        <span class="text-muted text-sm">—</span>
                                    @endif
                                </td>

                                {{-- Botón de acción --}}
                                <td class="actions">
                                    {{-- Aquí irá el enlace al detalle del paciente (próximamente) --}}
                                    <a href="{{ route('teacher.results.patient', $patient) }}" class="btn-action"
                                        title="Ver detalle">
                                        <i data-lucide="eye"></i>
                                    </a>

                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

</x-layouts.app>