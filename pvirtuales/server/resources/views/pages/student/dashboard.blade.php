{{--
|--------------------------------------------------------------------------
| Dashboard del Alumno
|--------------------------------------------------------------------------
|
| Vista principal del alumno tras el login.
| Usa el mismo layout que el profesor (sidebar + topbar + content-area).
|
| SECCIONES:
| 1. Stats: 4 métricas personales
| 2. Pacientes para practicar: tabla con disponibles e intentos restantes
| 3. Actividad reciente: últimas 5 simulaciones
| 4. Tests pendientes: call-to-action si hay simulaciones sin completar
| 5. Resultados: últimos tests completados con nota
| 6. Mis asignaturas: lista de asignaturas matriculadas
|
| DATOS RECIBIDOS DEL CONTROLADOR:
| $enrolledSubjectsCount → int
| $availablePatientsCount → int
| $simulationsCount → int
| $avgGrade → float|null
| $availablePatients → Collection<Patient> (con attempts_used)
| $recentActivity → Collection<TestAttempt>
| $pendingTests → Collection<TestAttempt>
| $completedTests → Collection<TestAttempt>
| $enrolledSubjects → Collection<Subject> (con available_patients_count)
|
--}}

                    <x-layouts.app>

                        <x-slot name="title">Dashboard</x-slot>

                        {{-- Reutiliza el mismo CSS del dashboard del profesor --}}
                        <x-slot name="styles">
                            <link href="{{ asset('css/dashboard.css') }}" rel="stylesheet">
                            <link href="{{ asset('css/modal.css') }}" rel="stylesheet">
                        </x-slot>

                        {{-- Topbar: sin botón de acción, el alumno no crea contenido --}}
                        <x-slot name="topbar">
                            <div class="topbar">
                                <div class="topbar-left">
                                    <div class="topbar-title">
                                        Bienvenido, {{ Auth::user()->first_name }} 👋
                                    </div>
                                    <div class="topbar-subtitle">
                                        {{ now()->isoFormat('dddd, D [de] MMMM [de] YYYY') }}
                                    </div>
                                </div>
                            </div>
                        </x-slot>

                        {{-- ================================================================
                        1. STATS — Métricas personales del alumno
                        ================================================================ --}}
                        <div class="stats-grid">

                            {{-- Asignaturas en las que está matriculado --}}
                            <div class="stat-card">
                                <div class="stat-card-icon primary">
                                    <i data-lucide="book-open"></i>
                                </div>
                                <div class="stat-card-info">
                                    <div class="stat-card-value">{{ $enrolledSubjectsCount }}</div>
                                    <div class="stat-card-label">Asignaturas</div>
                                </div>
                            </div>

                            {{-- Pacientes publicados accesibles --}}
                            <div class="stat-card">
                                <div class="stat-card-icon secondary">
                                    <i data-lucide="user-round"></i>
                                </div>
                                <div class="stat-card-info">
                                    <div class="stat-card-value">{{ $availablePatientsCount }}</div>
                                    <div class="stat-card-label">Pacientes disponibles</div>
                                </div>
                            </div>

                            {{-- Total de simulaciones realizadas --}}
                            <div class="stat-card">
                                <div class="stat-card-icon warning">
                                    <i data-lucide="message-square"></i>
                                </div>
                                <div class="stat-card-info">
                                    <div class="stat-card-value">{{ $simulationsCount }}</div>
                                    <div class="stat-card-label">Simulaciones realizadas</div>
                                </div>
                            </div>

                            {{-- Nota media (guión si aún no tiene ningún test completado) --}}
                            <div class="stat-card">
                                <div class="stat-card-icon danger">
                                    <i data-lucide="trending-up"></i>
                                </div>
                                <div class="stat-card-info">
                                    <div class="stat-card-value">
                                        {{ $avgGrade !== null ? number_format($avgGrade, 1) : '—' }}
                                    </div>
                                    <div class="stat-card-label">Nota media</div>
                                </div>
                            </div>

                        </div>

                        {{-- ================================================================
                        2. PACIENTES PARA PRACTICAR — Sección principal de acción
                        ================================================================ --}}
                        <div class="card mt-lg">
                            <div class="card-header">
                                <div class="card-header-title">
                                    <i data-lucide="stethoscope"></i>
                                    Pacientes para practicar
                                </div>
                            </div>

                            @if($availablePatients->isEmpty())
                                {{-- Sin pacientes: no está matriculado o ya agotó todos los intentos --}}
                                <div class="empty-state">
                                    <div class="empty-state-icon">
                                        <i data-lucide="user-round-x"></i>
                                    </div>
                                    <div class="empty-state-title">No hay pacientes disponibles</div>
                                    <div class="empty-state-text">
                                        Puede que no estés matriculado en ninguna asignatura o que hayas
                                        agotado los intentos permitidos en todos los pacientes.
                                    </div>
                                </div>
                            @else
                                <div class="table-wrapper" style="border: none; border-radius: 0;">
                                    <table>
                                        <thead>
                                            <tr>
                                                <th>Paciente</th>
                                                <th>Asignatura</th>
                                                <th>Modo</th>
                                                <th>Intentos</th>
                                                <th class="actions">Acción</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($availablePatients as $patient)
                                                @php
                                                    $isUnlimited = $patient->max_attempts === -1;
                                                    $remaining = $isUnlimited ? null : ($patient->max_attempts - $patient->attempts_used);
                                                    $simUrl = route('simulation.start', ['aiModel' => 'claude', 'patientId' => $patient->id]);
                                                @endphp
                                                <tr>
                                                    {{-- Nombre del caso y descripción breve --}}
                                                    <td>
                                                        <div class="patient-name">{{ $patient->case_title }}</div>
                                                        @if($patient->patient_description)
                                                            <div class="patient-desc">{{ $patient->patient_description }}</div>
                                                        @endif
                                                    </td>

                                                    {{-- Asignatura a la que pertenece el paciente --}}
                                                    <td>
                                                        <span class="text-muted text-sm">
                                                            {{ $patient->subject?->name ?? '—' }}
                                                        </span>
                                                    </td>

                                                    {{-- Modo de creación del paciente --}}
                                                    <td>
                                                        @if($patient->mode === 'basic')
                                                            <span class="badge badge-secondary">Básico</span>
                                                        @else
                                                            <span class="badge badge-primary">Avanzado</span>
                                                        @endif
                                                    </td>

                                                    {{-- Intentos: ∞ si ilimitados, X/max si limitados --}}
                                                    <td>
                                                        @if($isUnlimited)
                                                            <span class="badge badge-secondary">∞ ilimitados</span>
                                                        @else
                                                            <span class="badge badge-secondary">
                                                                {{ $patient->attempts_used }} / {{ $patient->max_attempts }}
                                                            </span>
                                                        @endif
                                                    </td>

                                                    {{-- Botón que abre el modal de confirmación antes de simular --}}
                                                    <td class="actions">
                                                        <button class="btn btn-primary btn-sm" onclick="openSimModal(
                                                                                        '{{ $simUrl }}',
                                                                                        {{ $isUnlimited ? 'true' : 'false' }},
                                                                                        {{ $remaining ?? 0 }}
                                                                                    )">
                                                            <i data-lucide="play"></i>
                                                            Simular
                                                        </button>
                                                    </td>

                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @endif
                        </div>

                        {{-- ================================================================
                        3. ACTIVIDAD RECIENTE — Últimas 5 simulaciones del alumno
                        ================================================================ --}}
                        <div class="card mt-lg">
                            <div class="card-header">
                                <div class="card-header-title">
                                    <i data-lucide="activity"></i>
                                    Actividad reciente
                                </div>
                            </div>

                            @if($recentActivity->isEmpty())
                                <div class="empty-state">
                                    <div class="empty-state-icon">
                                        <i data-lucide="clock"></i>
                                    </div>
                                    <div class="empty-state-title">Sin actividad todavía</div>
                                    <div class="empty-state-text">
                                        Aquí aparecerán tus últimas simulaciones cuando completes la primera.
                                    </div>
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
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($recentActivity as $attempt)
                                                <tr>
                                                    <td>
                                                        <div class="patient-name">
                                                            {{ $attempt->patient?->case_title ?? '—' }}
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <span class="text-muted text-sm">
                                                            {{ $attempt->patient?->subject?->name ?? '—' }}
                                                        </span>
                                                    </td>
                                                    <td class="text-muted text-sm">
                                                        {{ $attempt->created_at->diffForHumans() }}
                                                    </td>

                                                    {{-- Verde si tiene nota, amarillo si aún le falta el test --}}
                                                    <td>
                                                        @if($attempt->final_score !== null)
                                                            <span class="badge badge-success">
                                                                <i data-lucide="check"></i>
                                                                Completado
                                                            </span>
                                                        @else
                                                            <span class="badge badge-warning">
                                                                <i data-lucide="clock"></i>
                                                                Pendiente
                                                            </span>
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @endif
                        </div>

                        <!-- {{-- ================================================================
                        4. TESTS PENDIENTES — Solo visible si hay alguno sin completar
                        Call-to-action claro para que el alumno no lo olvide
                        ================================================================ --}}
                        @if($pendingTests->isNotEmpty())
                            <div class="card mt-lg">
                                <div class="card-header">
                                    <div class="card-header-title">
                                        <i data-lucide="clipboard-check"></i>
                                        Tests pendientes
                                    </div>
                                </div>

                                <div class="table-wrapper" style="border: none; border-radius: 0;">
                                    <table>
                                        <thead>
                                            <tr>
                                                <th>Paciente</th>
                                                <th>Asignatura</th>
                                                <th>Fecha simulación</th>
                                                <th class="actions">Acción</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($pendingTests as $attempt)
                                                <tr>
                                                    <td>
                                                        <div class="patient-name">
                                                            {{ $attempt->patient?->case_title ?? '—' }}
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <span class="text-muted text-sm">
                                                            {{ $attempt->patient?->subject?->name ?? '—' }}
                                                        </span>
                                                    </td>
                                                    <td class="text-muted text-sm">
                                                        {{ $attempt->created_at->diffForHumans() }}
                                                    </td>
                                                    <td class="actions">
                                                        {{-- Solo muestra el botón si el paciente tiene preguntas de test --}}
                                                        @if($attempt->patient?->hasTest())
                                                            <a href="{{ route('patients.test.take', $attempt->patient) }}"
                                                                class="btn btn-primary btn-sm">
                                                                <i data-lucide="clipboard-check"></i>
                                                                Completar test
                                                            </a>
                                                        @else
                                                            <span class="text-muted text-sm">Sin test asignado</span>
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        @endif -->

                        {{-- ================================================================
                        5. RESULTADOS — Últimos 5 tests completados con nota
                        ================================================================ --}}
                        <div class="card mt-lg">
                            <div class="card-header">
                                <div class="card-header-title">
                                    <i data-lucide="award"></i>
                                    Resultados
                                </div>
                            </div>

                            @if($completedTests->isEmpty())
                                <div class="empty-state">
                                    <div class="empty-state-icon">
                                        <i data-lucide="clipboard-x"></i>
                                    </div>
                                    <div class="empty-state-title">Sin resultados todavía</div>
                                    <div class="empty-state-text">
                                        Aquí aparecerán tus notas cuando completes los tests.
                                    </div>
                                </div>
                            @else
                                <div class="table-wrapper" style="border: none; border-radius: 0;">
                                    <table>
                                        <thead>
                                            <tr>
                                                <th>Paciente</th>
                                                <th>Asignatura</th>
                                                <th>Nota</th>
                                                <th>Fecha</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($completedTests as $attempt)
                                                <tr>
                                                    <td>
                                                        <div class="patient-name">
                                                            {{ $attempt->patient?->case_title ?? '—' }}
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <span class="text-muted text-sm">
                                                            {{ $attempt->patient?->subject?->name ?? '—' }}
                                                        </span>
                                                    </td>

                                                    {{-- Color del badge según nota: ≥70 verde, ≥50 amarillo, <50 gris --}} <td>
                                                        @php $score = $attempt->final_score @endphp
                                                        @if($score >= 70)
                                                            <span class="badge badge-success">{{ number_format($score, 1) }}</span>
                                                        @elseif($score >= 50)
                                                            <span class="badge badge-warning">{{ number_format($score, 1) }}</span>
                                                        @else
                                                            <span
                                                                class="badge badge-secondary">{{ number_format($score, 1) }}</span>
                                                        @endif
                                                        </td>

                                                        <td class="text-muted text-sm">
                                                            {{ $attempt->created_at->diffForHumans() }}
                                                        </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @endif
                        </div>

                        {{-- ================================================================
                        6. MIS ASIGNATURAS — Lista de asignaturas en las que está matriculado
                        ================================================================ --}}
                        <div class="card mt-lg">
                            <div class="card-header">
                                <div class="card-header-title">
                                    <i data-lucide="book-open"></i>
                                    Mis asignaturas
                                </div>
                            </div>

                            @if($enrolledSubjects->isEmpty())
                                <div class="empty-state">
                                    <div class="empty-state-icon">
                                        <i data-lucide="book-x"></i>
                                    </div>
                                    <div class="empty-state-title">No estás matriculado en ninguna asignatura</div>
                                    <div class="empty-state-text">
                                        Pide a tu profesor que te añada a su asignatura para poder acceder
                                        a los pacientes virtuales.
                                    </div>
                                </div>
                            @else
                                <div class="table-wrapper" style="border: none; border-radius: 0;">
                                    <table>
                                        <thead>
                                            <tr>
                                                <th>Asignatura</th>
                                                <th>Código</th>
                                                <th>Institución</th>
                                                <th>Pacientes disponibles</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($enrolledSubjects as $subject)
                                                <tr>
                                                    <td>
                                                        <div class="patient-name">{{ $subject->name }}</div>
                                                    </td>
                                                    <td>
                                                        <span class="text-muted text-sm">{{ $subject->code ?? '—' }}</span>
                                                    </td>
                                                    <td>
                                                        <span
                                                            class="text-muted text-sm">{{ $subject->institution ?? '—' }}</span>
                                                    </td>
                                                    <td>
                                                        <span class="badge badge-primary">
                                                            {{ $subject->available_patients_count }} pacientes
                                                        </span>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @endif
                        </div>

                        <div class="sim-modal-overlay" id="simModal">
                            <div class="sim-modal">
                                <div class="sim-modal-icon"><i data-lucide="stethoscope"></i></div>
                                <div class="sim-modal-title">¿Iniciar consulta?</div>
                                <p class="sim-modal-body" id="simModalMessage"></p>
                                <div class="sim-modal-actions">
                                    <button class="btn btn-ghost btn-sm" onclick="closeSimModal()">Cancelar</button>
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
                                document.getElementById('simModalConfirm').href = url;
                                if (isUnlimited) {
                                    msg.textContent = 'Estás a punto de iniciar una consulta. Puedes repetirla todas las veces que quieras.';
                                } else if (remaining === 1) {
                                    msg.textContent = 'Este es tu último intento disponible para este paciente.';
                                } else {
                                    msg.textContent = `Vas a usar 1 de los ${remaining} intentos que te quedan.`;
                                }
                                document.getElementById('simModal').classList.add('active');
                                lucide.createIcons();
                            }
                            function closeSimModal() {
                                document.getElementById('simModal').classList.remove('active');
                            }
                            document.getElementById('simModal').addEventListener('click', function (e) {
                                if (e.target === this) closeSimModal();
                            });
                        </script>


                    </x-layouts.app>