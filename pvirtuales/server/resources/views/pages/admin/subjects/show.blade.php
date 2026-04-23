<x-layouts.app>

    <x-slot name="title">Admin — {{ $subject->name }}</x-slot>
    <x-slot name="styles">
        <link href="{{ asset('css/dashboard.css') }}" rel="stylesheet">
        <link href="{{ asset('css/admin.css') }}" rel="stylesheet">
    </x-slot>

    <x-slot name="topbar">
        <div class="topbar">
            <div class="topbar-left">
                <div class="topbar-title">Detalle de Asignatura</div>
                <div class="topbar-subtitle">
                    <a href="{{ route('admin.subjects.index') }}" class="text-muted">Asignaturas</a>
                    &nbsp;/&nbsp; {{ $subject->name }}
                </div>
            </div>
            <div class="topbar-right">
                <a href="{{ route('admin.subjects.index') }}" class="btn btn-ghost btn-sm">
                    <i data-lucide="arrow-left"></i>
                    Volver
                </a>
            </div>
        </div>
    </x-slot>

    {{-- Cabecera --}}
    <div class="card">
        <div class="admin-profile-header">
            <div class="admin-subject-icon">
                <i data-lucide="book-open"></i>
            </div>
            <div class="admin-profile-info">
                <div class="admin-profile-name" id="subject-name">{{ $subject->name }}</div>
                <div class="admin-profile-meta">
                    @if($subject->code)
                        <span class="badge badge-neutral" id="subject-code">{{ $subject->code }}</span>
                    @endif
                    @if($subject->institution)
                        <span class="text-muted text-sm" id="subject-institution">
                            <i data-lucide="building-2" style="width:13px;height:13px;display:inline;vertical-align:middle;"></i>
                            {{ $subject->institution }}
                        </span>
                    @endif
                    <span class="text-muted text-sm">
                        <i data-lucide="graduation-cap" style="width:13px;height:13px;display:inline;vertical-align:middle;"></i>
                        Profesor: <strong id="subject-teacher">{{ $subject->creator?->full_name ?? '—' }}</strong>
                    </span>
                    <span class="text-muted text-sm">Creada {{ $subject->created_at->diffForHumans() }}</span>
                </div>
            </div>
            <div class="admin-profile-actions">
                <button type="button" class="btn btn-ghost btn-sm" id="btn-edit-subject">
                    <i data-lucide="pencil"></i>
                    Editar
                </button>
                <form action="{{ route('admin.subjects.destroy', $subject) }}" method="POST"
                    onsubmit="return confirm('¿Eliminar {{ addslashes($subject->name) }}? Se eliminarán todos sus pacientes y simulaciones en cascada.')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger btn-sm">
                        <i data-lucide="trash-2"></i>
                        Eliminar
                    </button>
                </form>
            </div>
        </div>
    </div>

    {{-- Stats rápidas --}}
    <div class="stats-grid" style="grid-template-columns: repeat(3, 1fr);">
        <div class="stat-card">
            <div class="stat-card-icon secondary"><i data-lucide="users"></i></div>
            <div class="stat-card-info">
                <div class="stat-card-value">{{ $subject->students->count() }}</div>
                <div class="stat-card-label">Alumnos matriculados</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-card-icon primary"><i data-lucide="user-round"></i></div>
            <div class="stat-card-info">
                <div class="stat-card-value">{{ $subject->patients->count() }}</div>
                <div class="stat-card-label">Pacientes virtuales</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-card-icon warning"><i data-lucide="users-round"></i></div>
            <div class="stat-card-info">
                <div class="stat-card-value">{{ $subject->collaborators->count() }}</div>
                <div class="stat-card-label">Colaboradores</div>
            </div>
        </div>
    </div>

    {{-- Alumnos --}}
    <div class="card">
        <div class="card-header">
            <div class="card-header-title"><i data-lucide="users"></i> Alumnos matriculados</div>
            <span class="badge badge-neutral">{{ $subject->students->count() }}</span>
        </div>
        @if($subject->students->isEmpty())
            <div class="empty-state">
                <div class="empty-state-icon"><i data-lucide="user-x"></i></div>
                <div class="empty-state-title">Sin alumnos matriculados</div>
            </div>
        @else
            <div class="table-wrapper" style="border:none;border-radius:0;">
                <table>
                    <thead><tr><th>Alumno</th><th>Email</th><th>Registro</th></tr></thead>
                    <tbody>
                        @foreach($subject->students as $student)
                            <tr>
                                <td>
                                    <div class="admin-user-row">
                                        <div class="admin-user-avatar" style="width:30px;height:30px;font-size:0.7rem;">
                                            {{ strtoupper(substr($student->first_name,0,1)) }}{{ strtoupper(substr($student->last_name,0,1)) }}
                                        </div>
                                        <div class="patient-name">{{ $student->full_name }}</div>
                                    </div>
                                </td>
                                <td><span class="text-muted text-sm">{{ $student->email }}</span></td>
                                <td><span class="text-muted text-sm">{{ $student->created_at->format('d/m/Y') }}</span></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    {{-- Pacientes --}}
    <div class="card">
        <div class="card-header">
            <div class="card-header-title"><i data-lucide="user-round"></i> Pacientes virtuales</div>
            <span class="badge badge-neutral">{{ $subject->patients->count() }}</span>
        </div>
        @if($subject->patients->isEmpty())
            <div class="empty-state">
                <div class="empty-state-icon"><i data-lucide="user-round-x"></i></div>
                <div class="empty-state-title">Sin pacientes</div>
            </div>
        @else
            <div class="table-wrapper" style="border:none;border-radius:0;">
                <table>
                    <thead><tr><th>Paciente</th><th>Modo</th><th>Estado</th><th>Creado</th></tr></thead>
                    <tbody>
                        @foreach($subject->patients as $patient)
                            <tr>
                                <td>
                                    <div class="patient-name">{{ $patient->case_title }}</div>
                                    @if($patient->identity?->patient_name)
                                        <div class="patient-desc">{{ $patient->identity->patient_name }}</div>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge {{ $patient->mode === 'basic' ? 'badge-secondary' : 'badge-primary' }}">
                                        {{ $patient->mode === 'basic' ? 'Básico' : 'Avanzado' }}
                                    </span>
                                </td>
                                <td>
                                    @if($patient->is_published)
                                        <span class="badge badge-success">Publicado</span>
                                    @else
                                        <span class="badge badge-warning">Borrador</span>
                                    @endif
                                </td>
                                <td class="text-muted text-sm">{{ $patient->created_at->format('d/m/Y') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    {{-- Colaboradores --}}
    @if($subject->collaborators->isNotEmpty())
    <div class="card">
        <div class="card-header">
            <div class="card-header-title"><i data-lucide="users-round"></i> Profesores colaboradores</div>
            <span class="badge badge-neutral">{{ $subject->collaborators->count() }}</span>
        </div>
        <div class="table-wrapper" style="border:none;border-radius:0;">
            <table>
                <thead><tr><th>Profesor</th><th>Email</th></tr></thead>
                <tbody>
                    @foreach($subject->collaborators as $collab)
                        <tr>
                            <td>
                                <div class="admin-user-row">
                                    <div class="admin-user-avatar" style="width:30px;height:30px;font-size:0.7rem;">
                                        {{ strtoupper(substr($collab->first_name,0,1)) }}{{ strtoupper(substr($collab->last_name,0,1)) }}
                                    </div>
                                    <div class="patient-name">{{ $collab->full_name }}</div>
                                </div>
                            </td>
                            <td><span class="text-muted text-sm">{{ $collab->email }}</span></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

    {{-- Modal de edición --}}
    <div class="admin-modal-overlay" id="modal-edit-subject">
        <div class="admin-modal">
            <div class="admin-modal-header">
                <div class="admin-modal-title">
                    <i data-lucide="pencil"></i>
                    Editar asignatura
                </div>
                <button type="button" class="admin-modal-close" id="btn-close-modal">
                    <i data-lucide="x"></i>
                </button>
            </div>

            <form id="form-edit-subject" novalidate>
                @csrf
                <div class="admin-modal-body">

                    <div id="modal-alert" class="alert" style="display:none;"></div>

                    <div class="admin-modal-row">
                        <div class="admin-modal-field">
                            <label class="form-hint" style="font-weight:600;color:var(--color-text);">Nombre</label>
                            <input type="text" name="name" id="field-name"
                                value="{{ $subject->name }}" class="form-input" required>
                        </div>
                        <div class="admin-modal-field">
                            <label class="form-hint" style="font-weight:600;color:var(--color-text);">Código</label>
                            <input type="text" name="code" id="field-code"
                                value="{{ $subject->code }}" class="form-input" placeholder="Ej: MED-101">
                        </div>
                    </div>

                    <div class="admin-modal-field mt-md">
                        <label class="form-hint" style="font-weight:600;color:var(--color-text);">Institución</label>
                        <input type="text" name="institution" id="field-institution"
                            value="{{ $subject->institution }}" class="form-input" placeholder="Ej: Universidad de Sevilla">
                    </div>

                    <div class="admin-modal-field mt-md">
                        <label class="form-hint" style="font-weight:600;color:var(--color-text);">Profesor responsable</label>
                        <select name="created_by_user_id" id="field-teacher" class="form-select">
                            @foreach($teachers as $teacher)
                                <option value="{{ $teacher->id }}"
                                    {{ $subject->created_by_user_id === $teacher->id ? 'selected' : '' }}>
                                    {{ $teacher->full_name }}
                                    {{ $teacher->isAdmin() ? '(Admin)' : '' }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                </div>

                <div class="admin-modal-footer">
                    <button type="button" class="btn btn-ghost btn-sm" id="btn-cancel-modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary btn-sm" id="btn-save-subject">
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
            const overlay   = document.getElementById('modal-edit-subject');
            const btnEdit   = document.getElementById('btn-edit-subject');
            const btnClose  = document.getElementById('btn-close-modal');
            const btnCancel = document.getElementById('btn-cancel-modal');
            const form      = document.getElementById('form-edit-subject');
            const alertEl   = document.getElementById('modal-alert');
            const PATCH_URL = '{{ route('admin.subjects.update', $subject) }}';

            function openModal()  { overlay.classList.add('active'); }
            function closeModal() { overlay.classList.remove('active'); hideAlert(); }
            function showAlert(msg, type = 'success') {
                alertEl.className = 'alert alert-' + type;
                alertEl.textContent = msg;
                alertEl.style.display = 'flex';
            }
            function hideAlert() { alertEl.style.display = 'none'; }

            btnEdit.addEventListener('click', openModal);
            btnClose.addEventListener('click', closeModal);
            btnCancel.addEventListener('click', closeModal);
            overlay.addEventListener('click', e => { if (e.target === overlay) closeModal(); });

            form.addEventListener('submit', function (e) {
                e.preventDefault();
                const btn = document.getElementById('btn-save-subject');
                btn.disabled = true;

                const data = new FormData(form);
                data.append('_method', 'PATCH');

                fetch(PATCH_URL, {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: data,
                })
                .then(r => r.json())
                .then(res => {
                    if (res.success) {
                        document.getElementById('subject-name').textContent    = res.name;
                        document.getElementById('subject-teacher').textContent = res.teacher_name;
                        const codeEl = document.getElementById('subject-code');
                        if (codeEl) codeEl.textContent = document.getElementById('field-code').value || '';
                        showAlert('Asignatura actualizada correctamente.');
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
