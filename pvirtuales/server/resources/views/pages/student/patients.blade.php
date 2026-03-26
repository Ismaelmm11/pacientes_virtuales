{{--
|--------------------------------------------------------------------------
| Pacientes disponibles para el alumno — "Practicar"
|--------------------------------------------------------------------------
|
| Muestra todos los pacientes publicados en las asignaturas del alumno,
| agrupados por asignatura. La única acción es "Simular".
| Antes de iniciar muestra un modal de confirmación que indica los
| intentos restantes (o avisa si son ilimitados).
|
| DATOS RECIBIDOS:
|   $patientsBySubject → Collection agrupada por subject_id
|   $enrolledSubjects  → Collection de Subject indexada por id
|
--}}

<x-layouts.app>

    <x-slot name="title">Pacientes — Practicar</x-slot>

    <x-slot name="styles">
        <link href="{{ asset('css/dashboard.css') }}" rel="stylesheet">
        <style>
            /* Modal de confirmación de simulación */
            .sim-modal-overlay {
                display: none;
                position: fixed;
                inset: 0;
                background: rgba(0, 0, 0, 0.55);
                z-index: 1000;
                align-items: center;
                justify-content: center;
            }
            .sim-modal-overlay.active { display: flex; }

            .sim-modal {
                background: var(--color-surface);
                border-radius: var(--radius-lg);
                border: 1px solid var(--color-border);
                box-shadow: var(--shadow-lg);
                padding: var(--spacing-xl);
                max-width: 420px;
                width: 90%;
                text-align: center;
            }
            .sim-modal-icon {
                width: 56px;
                height: 56px;
                border-radius: var(--radius-full);
                background: rgba(91, 231, 196, 0.15);
                color: var(--color-primary);
                display: flex;
                align-items: center;
                justify-content: center;
                margin: 0 auto var(--spacing-md);
            }
            .sim-modal-title {
                font-size: 1.1rem;
                font-weight: 700;
                color: var(--color-text);
                margin-bottom: var(--spacing-sm);
            }
            .sim-modal-body {
                font-size: 0.9rem;
                color: var(--color-text-muted);
                margin-bottom: var(--spacing-lg);
                line-height: 1.5;
            }
            .sim-modal-actions {
                display: flex;
                gap: var(--spacing-sm);
                justify-content: center;
            }
        </style>
    </x-slot>

    <x-slot name="topbar">
        <div class="topbar">
            <div class="topbar-left">
                <div class="topbar-title">Pacientes para practicar</div>
                <div class="topbar-subtitle">
                    Todos los pacientes virtuales disponibles en tus asignaturas
                </div>
            </div>
        </div>
    </x-slot>

    {{-- Sin pacientes disponibles --}}
    @if($patientsBySubject->isEmpty())
        <div class="card">
            <div class="empty-state">
                <div class="empty-state-icon">
                    <i data-lucide="user-round-x"></i>
                </div>
                <div class="empty-state-title">No hay pacientes disponibles</div>
                <div class="empty-state-text">
                    Puede que no estés matriculado en ninguna asignatura o que tu
                    profesor no haya publicado ningún paciente todavía.
                </div>
                <a href="{{ route('student.dashboard') }}" class="btn btn-primary mt-md">
                    <i data-lucide="layout-dashboard"></i>
                    Volver al dashboard
                </a>
            </div>
        </div>

    @else

        {{-- Una card por asignatura --}}
        @foreach($patientsBySubject as $subjectId => $patients)
            @php
                $subject = $enrolledSubjects->get($subjectId);
                // Cuenta cuántos tienen intentos disponibles (incluye ilimitados)
                $availableCount = $patients->filter(
                    fn($p) => $p->max_attempts === -1 || $p->attempts_used < $p->max_attempts
                )->count();
            @endphp

            <div class="card mt-lg">
                <div class="card-header">
                    <div class="card-header-title">
                        <i data-lucide="book-open"></i>
                        {{ $subject?->name ?? 'Asignatura' }}
                        @if($subject?->code)
                            <span class="text-muted text-sm">&nbsp;·&nbsp;{{ $subject->code }}</span>
                        @endif
                    </div>
                    <span class="badge badge-secondary">
                        {{ $availableCount }} disponibles
                    </span>
                </div>

                <div class="table-wrapper" style="border: none; border-radius: 0;">
                    <table>
                        <thead>
                            <tr>
                                <th>Paciente</th>
                                <th>Modo</th>
                                <th>Intentos</th>
                                <th>Estado</th>
                                <th class="actions">Acción</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($patients as $patient)
                                @php
                                    $isUnlimited = $patient->max_attempts === -1;
                                    $canSimulate = $isUnlimited || $patient->attempts_used < $patient->max_attempts;
                                    $remaining   = $isUnlimited ? null : ($patient->max_attempts - $patient->attempts_used);
                                    $simUrl      = route('simulation.start', ['aiModel' => 'claude', 'patientId' => $patient->id]);
                                @endphp
                                <tr>
                                    {{-- Nombre del caso --}}
                                    <td>
                                        <div class="patient-name">{{ $patient->case_title }}</div>
                                        @if($patient->patient_description)
                                            <div class="patient-desc">{{ $patient->patient_description }}</div>
                                        @endif
                                    </td>

                                    {{-- Modo --}}
                                    <td>
                                        @if($patient->mode === 'basic')
                                            <span class="badge badge-secondary">Básico</span>
                                        @else
                                            <span class="badge badge-primary">Avanzado</span>
                                        @endif
                                    </td>

                                    {{-- Intentos: "∞" si ilimitados, "X/max" si limitados --}}
                                    <td>
                                        @if($isUnlimited)
                                            <span class="badge badge-secondary">∞ ilimitados</span>
                                        @else
                                            <span class="badge {{ $canSimulate ? 'badge-secondary' : 'badge-warning' }}">
                                                {{ $patient->attempts_used }} / {{ $patient->max_attempts }}
                                            </span>
                                        @endif
                                    </td>

                                    {{-- Estado --}}
                                    <td>
                                        @if($canSimulate)
                                            <span class="badge badge-success">
                                                <i data-lucide="check"></i>
                                                Disponible
                                            </span>
                                        @else
                                            <span class="badge badge-warning">
                                                <i data-lucide="lock"></i>
                                                Agotado
                                            </span>
                                        @endif
                                    </td>

                                    {{-- Acción: solo Simular si puede, nada si agotado --}}
                                    <td class="actions">
                                        @if($canSimulate)
                                            <button class="btn btn-primary btn-sm"
                                                onclick="openSimModal(
                                                    '{{ $simUrl }}',
                                                    {{ $isUnlimited ? 'true' : 'false' }},
                                                    {{ $remaining ?? 0 }}
                                                )">
                                                <i data-lucide="play"></i>
                                                Simular
                                            </button>
                                        @else
                                            <span class="text-muted text-sm">—</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endforeach

    @endif

    {{-- ================================================================
         Modal de confirmación antes de iniciar la simulación
         ================================================================ --}}
    <div class="sim-modal-overlay" id="simModal">
        <div class="sim-modal">
            <div class="sim-modal-icon">
                <i data-lucide="stethoscope"></i>
            </div>
            <div class="sim-modal-title">¿Iniciar consulta?</div>
            <p class="sim-modal-body" id="simModalMessage"></p>
            <div class="sim-modal-actions">
                <button class="btn btn-ghost btn-sm" onclick="closeSimModal()">
                    Cancelar
                </button>
                <a class="btn btn-primary btn-sm" id="simModalConfirm">
                    <i data-lucide="play"></i>
                    Iniciar consulta
                </a>
            </div>
        </div>
    </div>

    <script>
        function openSimModal(url, isUnlimited, remaining) {
            const msg = document.getElementById('simModalMessage');
            const btn = document.getElementById('simModalConfirm');

            if (isUnlimited) {
                msg.textContent = 'Estás a punto de iniciar una consulta con este paciente virtual. Puedes repetirla todas las veces que quieras.';
            } else if (remaining === 1) {
                msg.textContent = 'Este es tu último intento disponible para este paciente. Una vez iniciada la consulta no podrás volver a simularla.';
            } else {
                msg.textContent = `Vas a usar 1 de los ${remaining} intentos que te quedan para este paciente.`;
            }

            btn.href = url;
            document.getElementById('simModal').classList.add('active');
            lucide.createIcons(); // refresca el icono del modal
        }

        function closeSimModal() {
            document.getElementById('simModal').classList.remove('active');
        }

        // Cerrar modal al hacer clic fuera
        document.getElementById('simModal').addEventListener('click', function(e) {
            if (e.target === this) closeSimModal();
        });
    </script>

</x-layouts.app>
