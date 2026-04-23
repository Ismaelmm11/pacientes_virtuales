<x-layouts.app>

    <x-slot name="title">Admin — {{ $user->full_name }}</x-slot>

    <x-slot name="styles">
        <link href="{{ asset('css/dashboard.css') }}" rel="stylesheet">
        <link href="{{ asset('css/admin.css') }}" rel="stylesheet">
    </x-slot>

    <x-slot name="topbar">
        <div class="topbar">
            <div class="topbar-left">
                <div class="topbar-title">Detalle de Usuario</div>
                <div class="topbar-subtitle">
                    <a href="{{ route('admin.users.index') }}" class="text-muted">Usuarios</a>
                    &nbsp;/&nbsp; {{ $user->full_name }}
                </div>
            </div>
            <div class="topbar-right">
                <a href="{{ route('admin.users.index') }}" class="btn btn-ghost btn-sm">
                    <i data-lucide="arrow-left"></i>
                    Volver
                </a>
            </div>
        </div>
    </x-slot>

    {{-- ---- PERFIL ---- --}}
    <div class="card">
        <div class="admin-profile-header">

            {{-- Avatar --}}
            <div class="admin-profile-avatar" id="profile-avatar">
                <span id="avatar-initials">
                    {{ strtoupper(substr($user->first_name, 0, 1)) }}{{ strtoupper(substr($user->last_name, 0, 1)) }}
                </span>
                {{-- Future: <img src="{{ $user->avatar_url }}" alt="{{ $user->full_name }}"> --}}
            </div>

            {{-- Info --}}
            <div class="admin-profile-info">
                <div class="admin-profile-name" id="profile-name">{{ $user->full_name }}</div>
                <div class="admin-profile-meta">
                    <span id="profile-role-badge">
                        @if($user->isAdmin())
                            <span class="badge badge-warning">Admin</span>
                        @elseif($user->isTeacher())
                            <span class="badge badge-primary">Profesor</span>
                        @else
                            <span class="badge badge-secondary">Alumno</span>
                        @endif
                    </span>
                    <span class="text-muted text-sm">{{ $user->email }}</span>
                    @if($user->birth_date)
                        <span class="text-muted text-sm">
                            <i data-lucide="calendar" style="width:13px;height:13px;display:inline;vertical-align:middle;"></i>
                            {{ $user->birth_date->format('d/m/Y') }}
                        </span>
                    @endif
                    <span class="text-muted text-sm">
                        <i data-lucide="clock" style="width:13px;height:13px;display:inline;vertical-align:middle;"></i>
                        Registrado {{ $user->created_at->diffForHumans() }}
                    </span>
                    <span class="badge badge-neutral">{{ $user->auth_provider ?? 'local' }}</span>
                </div>
            </div>

            {{-- Acciones --}}
            <div class="admin-profile-actions">
                <button type="button" class="btn btn-ghost btn-sm" id="btn-edit-user">
                    <i data-lucide="pencil"></i>
                    Editar
                </button>
                @if($user->id !== auth()->id())
                    <form action="{{ route('admin.users.destroy', $user) }}" method="POST"
                        onsubmit="return confirm('¿Eliminar a {{ addslashes($user->full_name) }}? Esta acción eliminará también todos sus datos (asignaturas, pacientes, simulaciones).')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger btn-sm">
                            <i data-lucide="trash-2"></i>
                            Eliminar
                        </button>
                    </form>
                @endif
            </div>

        </div>
    </div>

    {{-- ---- SECCIÓN ESPECÍFICA POR ROL ---- --}}

    @if($user->isTeacher() || $user->isAdmin())

        {{-- Asignaturas creadas --}}
        <div class="card">
            <div class="card-header">
                <div class="card-header-title">
                    <i data-lucide="book-open"></i>
                    Asignaturas creadas
                </div>
                <span class="badge badge-neutral">{{ $subjects->count() }}</span>
            </div>
            @if($subjects->isEmpty())
                <div class="empty-state">
                    <div class="empty-state-icon"><i data-lucide="book-x"></i></div>
                    <div class="empty-state-title">Sin asignaturas</div>
                </div>
            @else
                <div class="table-wrapper" style="border:none;border-radius:0;">
                    <table>
                        <thead>
                            <tr>
                                <th>Nombre</th>
                                <th>Código</th>
                                <th>Institución</th>
                                <th>Pacientes</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($subjects as $subject)
                                <tr>
                                    <td><div class="patient-name">{{ $subject->name }}</div></td>
                                    <td><span class="text-muted text-sm">{{ $subject->code ?? '—' }}</span></td>
                                    <td><span class="text-muted text-sm">{{ $subject->institution ?? '—' }}</span></td>
                                    <td><span class="badge badge-primary">{{ $subject->patients_count }}</span></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

        {{-- Pacientes creados --}}
        <div class="card">
            <div class="card-header">
                <div class="card-header-title">
                    <i data-lucide="user-round"></i>
                    Pacientes creados
                </div>
                <span class="badge badge-neutral">{{ $patients->count() }}</span>
            </div>
            @if($patients->isEmpty())
                <div class="empty-state">
                    <div class="empty-state-icon"><i data-lucide="user-round-x"></i></div>
                    <div class="empty-state-title">Sin pacientes</div>
                </div>
            @else
                <div class="table-wrapper" style="border:none;border-radius:0;">
                    <table>
                        <thead>
                            <tr>
                                <th>Paciente</th>
                                <th>Asignatura</th>
                                <th>Estado</th>
                                <th>Simulaciones</th>
                                <th>Creado</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($patients as $patient)
                                <tr>
                                    <td><div class="patient-name">{{ $patient->case_title }}</div></td>
                                    <td><span class="text-muted text-sm">{{ $patient->subject?->name ?? '—' }}</span></td>
                                    <td>
                                        @if($patient->is_published)
                                            <span class="badge badge-success">Publicado</span>
                                        @else
                                            <span class="badge badge-warning">Borrador</span>
                                        @endif
                                    </td>
                                    <td><span class="badge badge-secondary">{{ $patient->test_attempts_count }}</span></td>
                                    <td class="text-muted text-sm">{{ $patient->created_at->format('d/m/Y') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

    @else

        {{-- Asignaturas matriculadas --}}
        <div class="card">
            <div class="card-header">
                <div class="card-header-title">
                    <i data-lucide="book-open"></i>
                    Asignaturas matriculadas
                </div>
                <span class="badge badge-neutral">{{ $enrolledSubjects->count() }}</span>
            </div>
            @if($enrolledSubjects->isEmpty())
                <div class="empty-state">
                    <div class="empty-state-icon"><i data-lucide="book-x"></i></div>
                    <div class="empty-state-title">Sin asignaturas</div>
                </div>
            @else
                <div class="table-wrapper" style="border:none;border-radius:0;">
                    <table>
                        <thead>
                            <tr><th>Asignatura</th><th>Código</th><th>Institución</th></tr>
                        </thead>
                        <tbody>
                            @foreach($enrolledSubjects as $subject)
                                <tr>
                                    <td><div class="patient-name">{{ $subject->name }}</div></td>
                                    <td><span class="text-muted text-sm">{{ $subject->code ?? '—' }}</span></td>
                                    <td><span class="text-muted text-sm">{{ $subject->institution ?? '—' }}</span></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

        {{-- Historial de simulaciones --}}
        <div class="card">
            <div class="card-header">
                <div class="card-header-title">
                    <i data-lucide="message-square"></i>
                    Historial de simulaciones
                </div>
                <span class="badge badge-neutral">{{ $testAttempts->count() }}</span>
            </div>
            @if($testAttempts->isEmpty())
                <div class="empty-state">
                    <div class="empty-state-icon"><i data-lucide="clock"></i></div>
                    <div class="empty-state-title">Sin simulaciones</div>
                </div>
            @else
                <div class="table-wrapper" style="border:none;border-radius:0;">
                    <table>
                        <thead>
                            <tr><th>Paciente</th><th>Estado</th><th>Nota</th><th>Fecha</th></tr>
                        </thead>
                        <tbody>
                            @foreach($testAttempts as $attempt)
                                <tr>
                                    <td><div class="patient-name">{{ $attempt->patient?->case_title ?? '—' }}</div></td>
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
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

    @endif

    {{-- ================================================================
    MODAL DE EDICIÓN
    ================================================================ --}}
    <div class="admin-modal-overlay" id="modal-edit-user">
        <div class="admin-modal">

            <div class="admin-modal-header">
                <div class="admin-modal-title">
                    <i data-lucide="pencil"></i>
                    Editar usuario
                </div>
                <button type="button" class="admin-modal-close" id="btn-close-modal">
                    <i data-lucide="x"></i>
                </button>
            </div>

            <form id="form-edit-user" novalidate>
                @csrf

                <div class="admin-modal-body">

                    <div id="modal-alert" class="alert alert-success" style="display:none;"></div>

                    <div class="admin-modal-row">
                        <div class="admin-modal-field">
                            <label class="form-hint" style="font-weight:600;color:var(--color-text);">Nombre</label>
                            <input type="text" name="first_name" id="field-first-name"
                                value="{{ $user->first_name }}"
                                class="form-input" required>
                        </div>
                        <div class="admin-modal-field">
                            <label class="form-hint" style="font-weight:600;color:var(--color-text);">Apellidos</label>
                            <input type="text" name="last_name" id="field-last-name"
                                value="{{ $user->last_name }}"
                                class="form-input" required>
                        </div>
                    </div>

                    <div class="admin-modal-field mt-md">
                        <label class="form-hint" style="font-weight:600;color:var(--color-text);">Email</label>
                        <input type="email" name="email" id="field-email"
                            value="{{ $user->email }}"
                            class="form-input" required>
                    </div>

                    <div class="admin-modal-field mt-md">
                        <label class="form-hint" style="font-weight:600;color:var(--color-text);">Rol</label>
                        <select name="role_id" id="field-role" class="form-select">
                            <option value="1" {{ $user->role_id == 1 ? 'selected' : '' }}>Alumno</option>
                            <option value="2" {{ $user->role_id == 2 ? 'selected' : '' }}>Profesor</option>
                            <option value="3" {{ $user->role_id == 3 ? 'selected' : '' }}>Admin</option>
                        </select>
                    </div>

                </div>

                <div class="admin-modal-footer">
                    <button type="button" class="btn btn-ghost btn-sm" id="btn-cancel-modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary btn-sm" id="btn-save-user">
                        <i data-lucide="check"></i>
                        Guardar cambios
                    </button>
                </div>
            </form>

        </div>
    </div>

    <x-slot name="scripts">
        <script>
        (function () {
            const overlay   = document.getElementById('modal-edit-user');
            const btnEdit   = document.getElementById('btn-edit-user');
            const btnClose  = document.getElementById('btn-close-modal');
            const btnCancel = document.getElementById('btn-cancel-modal');
            const form      = document.getElementById('form-edit-user');
            const alert     = document.getElementById('modal-alert');
            const PATCH_URL = '{{ route('admin.users.update', $user) }}';

            const roleLabels = { 1: 'Alumno', 2: 'Profesor', 3: 'Admin' };
            const roleBadges = {
                1: '<span class="badge badge-secondary">Alumno</span>',
                2: '<span class="badge badge-primary">Profesor</span>',
                3: '<span class="badge badge-warning">Admin</span>',
            };

            function openModal()  { overlay.classList.add('active'); }
            function closeModal() { overlay.classList.remove('active'); hideAlert(); }

            function showAlert(msg, type = 'success') {
                alert.className = 'alert alert-' + type;
                alert.textContent = msg;
                alert.style.display = 'flex';
            }
            function hideAlert() { alert.style.display = 'none'; }

            btnEdit.addEventListener('click', openModal);
            btnClose.addEventListener('click', closeModal);
            btnCancel.addEventListener('click', closeModal);
            overlay.addEventListener('click', e => { if (e.target === overlay) closeModal(); });

            form.addEventListener('submit', function (e) {
                e.preventDefault();
                const btn = document.getElementById('btn-save-user');
                btn.disabled = true;

                const data = new FormData(form);
                data.append('_method', 'PATCH');

                fetch(PATCH_URL, {
                    method: 'POST',
                    headers: { 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                    body: data,
                })
                .then(r => r.json())
                .then(res => {
                    if (res.success) {
                        // Actualizar nombre en el perfil sin recargar
                        const firstName = document.getElementById('field-first-name').value.trim();
                        const lastName  = document.getElementById('field-last-name').value.trim();
                        const roleId    = parseInt(document.getElementById('field-role').value);
                        const initials  = firstName.charAt(0).toUpperCase() + lastName.charAt(0).toUpperCase();

                        document.getElementById('profile-name').textContent    = firstName + ' ' + lastName;
                        document.getElementById('avatar-initials').textContent = initials;
                        document.getElementById('profile-role-badge').innerHTML = roleBadges[roleId] ?? '';

                        showAlert('Usuario actualizado correctamente.');
                        lucide.createIcons();
                        setTimeout(closeModal, 1200);
                    } else {
                        showAlert('Error al guardar los cambios.', 'danger');
                    }
                })
                .catch(() => showAlert('Error de conexión.', 'danger'))
                .finally(() => btn.disabled = false);
            });
        })();
        </script>
    </x-slot>

</x-layouts.app>
