<x-layouts.app>

    <x-slot name="title">Admin — Usuarios</x-slot>

    <x-slot name="styles">
        <link href="{{ asset('css/dashboard.css') }}" rel="stylesheet">
        <link href="{{ asset('css/admin.css') }}" rel="stylesheet">
    </x-slot>

    <x-slot name="topbar">
        <div class="topbar">
            <div class="topbar-left">
                <div class="topbar-title">Gestión de Usuarios</div>
                <div class="topbar-subtitle">Todos los usuarios de la plataforma</div>
            </div>
            <div class="topbar-right">
                <button type="button" class="btn btn-primary btn-sm" id="btn-new-user">
                    <i data-lucide="user-plus"></i>
                    Nuevo usuario
                </button>
            </div>
        </div>
    </x-slot>


    {{-- Stats --}}
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-card-icon primary"><i data-lucide="graduation-cap"></i></div>
            <div class="stat-card-info">
                <div class="stat-card-value">{{ $totalTeachers }}</div>
                <div class="stat-card-label">Profesores</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-card-icon secondary"><i data-lucide="users"></i></div>
            <div class="stat-card-info">
                <div class="stat-card-value">{{ $totalStudents }}</div>
                <div class="stat-card-label">Alumnos</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-card-icon warning"><i data-lucide="shield-check"></i></div>
            <div class="stat-card-info">
                <div class="stat-card-value">{{ $totalAdmins }}</div>
                <div class="stat-card-label">Administradores</div>
            </div>
        </div>
    </div>

    <div class="card">

        {{-- Filtros (fuera del contenedor AJAX, no se reemplazan) --}}
        <div class="card-body-form">
            <div class="admin-filters">

                {{-- Tabs de rol --}}
                <div class="admin-filter-tabs">
                    <button type="button" class="admin-filter-tab {{ !request('rol') ? 'active' : '' }}"
                        data-rol-filter="">
                        Todos
                    </button>
                    <button type="button"
                        class="admin-filter-tab {{ request('rol') == \App\Models\Role::TEACHER_ID ? 'active' : '' }}"
                        data-rol-filter="{{ \App\Models\Role::TEACHER_ID }}">
                        Profesores
                    </button>
                    <button type="button"
                        class="admin-filter-tab {{ request('rol') == \App\Models\Role::STUDENT_ID ? 'active' : '' }}"
                        data-rol-filter="{{ \App\Models\Role::STUDENT_ID }}">
                        Alumnos
                    </button>
                    <button type="button"
                        class="admin-filter-tab {{ request('rol') == \App\Models\Role::ADMIN_ID ? 'active' : '' }}"
                        data-rol-filter="{{ \App\Models\Role::ADMIN_ID }}">
                        Admins
                    </button>
                </div>

                {{-- Derecha: búsqueda + per_page --}}
                <div class="admin-filters-right">

                    {{-- Per page --}}
                    <div class="admin-per-page">
                        <span>Ver</span>
                        <select id="per-page-select">
                            @foreach([10, 20, 50, 100] as $option)
                                <option value="{{ $option }}" {{ $perPage == $option ? 'selected' : '' }}>
                                    {{ $option }}
                                </option>
                            @endforeach
                        </select>
                        <span>por página</span>
                    </div>

                    {{-- Búsqueda --}}
                    <div class="admin-filter-search">
                        <i data-lucide="search"></i>
                        <input type="text" id="search-input" value="{{ request('buscar') }}"
                            placeholder="Buscar por nombre o email..." class="admin-search-input" autocomplete="off">
                        <button type="button" id="search-clear"
                            class="admin-search-clear {{ request('buscar') ? '' : 'hidden' }}" title="Limpiar búsqueda">
                            <i data-lucide="x"></i>
                        </button>
                    </div>

                </div>

            </div>
        </div>

        {{-- Contenedor AJAX — solo esta parte se reemplaza --}}
        <div id="users-table-container">
            @include('pages.admin.users._table', ['users' => $users, 'perPage' => $perPage])
        </div>

    </div>

    {{-- Modal de creación de usuario --}}
    <div class="admin-modal-overlay" id="modal-create-user">
        <div class="admin-modal">

            <div class="admin-modal-header">
                <div class="admin-modal-title">
                    <i data-lucide="user-plus"></i>
                    Nuevo usuario
                </div>
                <button type="button" class="admin-modal-close" id="btn-close-create-modal">
                    <i data-lucide="x"></i>
                </button>
            </div>

            <form id="form-create-user" novalidate>
                @csrf

                <div class="admin-modal-body">

                    <div id="create-modal-alert" class="alert" style="display:none;"></div>

                    <div class="admin-modal-row">
                        <div class="admin-modal-field">
                            <label class="form-hint" style="font-weight:600;color:var(--color-text);">Nombre</label>
                            <input type="text" name="first_name" class="form-input" placeholder="Ej: Ana" required>
                        </div>
                        <div class="admin-modal-field">
                            <label class="form-hint" style="font-weight:600;color:var(--color-text);">Apellidos</label>
                            <input type="text" name="last_name" class="form-input" placeholder="Ej: García López"
                                required>
                        </div>
                    </div>

                    <div class="admin-modal-field mt-md">
                        <label class="form-hint" style="font-weight:600;color:var(--color-text);">Email</label>
                        <input type="email" name="email" class="form-input" placeholder="correo@ejemplo.com" required>
                    </div>

                    <div class="admin-modal-field mt-md">
                        <label class="form-hint" style="font-weight:600;color:var(--color-text);">Rol</label>
                        <select name="role_id" class="form-select">
                            <option value="1">Alumno</option>
                            <option value="2">Profesor</option>
                            <option value="3">Admin</option>
                        </select>
                    </div>

                    <div class="admin-modal-row mt-md">
                        <div class="admin-modal-field">
                            <label class="form-hint" style="font-weight:600;color:var(--color-text);">Contraseña</label>
                            <input type="password" name="password" class="form-input" placeholder="Mínimo 8 caracteres"
                                required>
                        </div>
                        <div class="admin-modal-field">
                            <label class="form-hint" style="font-weight:600;color:var(--color-text);">Confirmar
                                contraseña</label>
                            <input type="password" name="password_confirmation" class="form-input"
                                placeholder="Repetir contraseña" required>
                        </div>
                    </div>

                </div>

                <div class="admin-modal-footer">
                    <button type="button" class="btn btn-ghost btn-sm" id="btn-cancel-create-modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary btn-sm" id="btn-save-new-user">
                        <i data-lucide="user-plus"></i>
                        Crear usuario
                    </button>
                </div>
            </form>

        </div>
    </div>


    {{-- Estado de filtros activos (valor inicial para JS) --}}
    <input type="hidden" id="rol-input" value="{{ request('rol', '') }}">

    <x-slot name="scripts">
        <script>
            (function () {
                const BASE_URL = '{{ route('admin.users.index') }}';
                const container = document.getElementById('users-table-container');
                const searchInput = document.getElementById('search-input');
                const searchClear = document.getElementById('search-clear');
                const rolInput = document.getElementById('rol-input');
                const perPageSelect = document.getElementById('per-page-select');

                // Construye la URL con los filtros actuales
                function buildUrl(overrides = {}) {
                    const params = new URLSearchParams();
                    const buscar = searchInput.value.trim();
                    const rol = rolInput.value;
                    const perPage = perPageSelect.value;

                    if (buscar) params.set('buscar', buscar);
                    if (rol) params.set('rol', rol);
                    params.set('per_page', perPage);

                    Object.entries(overrides).forEach(([k, v]) => {
                        if (v === null || v === '') params.delete(k);
                        else params.set(k, v);
                    });

                    return BASE_URL + '?' + params.toString();
                }

                // Carga el partial vía AJAX y reemplaza el contenedor
                function loadTable(url) {
                    container.classList.add('table-loading');

                    fetch(url, {
                        headers: { 'X-Requested-With': 'XMLHttpRequest' }
                    })
                        .then(r => r.text())
                        .then(html => {
                            container.innerHTML = html;
                            history.pushState({}, '', url);
                            lucide.createIcons();
                            container.classList.remove('table-loading');
                        })
                        .catch(() => container.classList.remove('table-loading'));
                }

                // Búsqueda con debounce 350ms
                let searchTimer;
                searchInput.addEventListener('input', function () {
                    clearTimeout(searchTimer);
                    searchClear.classList.toggle('hidden', !this.value.trim());
                    searchTimer = setTimeout(() => loadTable(buildUrl({ page: null })), 350);
                });

                // Limpiar búsqueda
                searchClear.addEventListener('click', function () {
                    searchInput.value = '';
                    this.classList.add('hidden');
                    loadTable(buildUrl({ page: null }));
                });

                // Tabs de rol
                document.querySelectorAll('[data-rol-filter]').forEach(tab => {
                    tab.addEventListener('click', function () {
                        rolInput.value = this.dataset.rolFilter;
                        document.querySelectorAll('[data-rol-filter]').forEach(t => t.classList.remove('active'));
                        this.classList.add('active');
                        loadTable(buildUrl({ page: null }));
                    });
                });

                // Per page
                perPageSelect.addEventListener('change', () => loadTable(buildUrl({ page: null })));

                // Paginación (delegación de eventos en el contenedor)
                container.addEventListener('click', function (e) {
                    const link = e.target.closest('a.pagination-btn');
                    if (!link) return;
                    e.preventDefault();
                    loadTable(link.href);
                });

                // ---- Modal de creación ----
                (function () {
                    const overlay = document.getElementById('modal-create-user');
                    const btnNew = document.getElementById('btn-new-user');
                    const btnClose = document.getElementById('btn-close-create-modal');
                    const btnCancel = document.getElementById('btn-cancel-create-modal');
                    const form = document.getElementById('form-create-user');
                    const alertEl = document.getElementById('create-modal-alert');
                    const STORE_URL = '{{ route('admin.users.store') }}';

                    function openModal() {
                        form.reset();
                        hideAlert();
                        overlay.classList.add('active');
                        lucide.createIcons();
                    }
                    function closeModal() {
                        overlay.classList.remove('active');
                    }
                    function showAlert(msg, type = 'success') {
                        alertEl.className = 'alert alert-' + type;
                        alertEl.textContent = msg;
                        alertEl.style.display = 'flex';
                    }
                    function hideAlert() { alertEl.style.display = 'none'; }

                    btnNew.addEventListener('click', openModal);
                    btnClose.addEventListener('click', closeModal);
                    btnCancel.addEventListener('click', closeModal);
                    overlay.addEventListener('click', e => { if (e.target === overlay) closeModal(); });

                    form.addEventListener('submit', function (e) {
                        e.preventDefault();
                        const btn = document.getElementById('btn-save-new-user');
                        btn.disabled = true;

                        fetch(STORE_URL, {
                            method: 'POST',
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: new FormData(form),
                        })
                            .then(r => r.json())
                            .then(res => {
                                if (res.success) {
                                    showAlert('Usuario creado correctamente.');
                                    setTimeout(() => {
                                        closeModal();
                                        loadTable(buildUrl());
                                    }, 900);
                                } else {
                                    const errors = res.errors
                                        ? Object.values(res.errors).flat().join(' · ')
                                        : 'Error al crear el usuario.';
                                    showAlert(errors, 'danger');
                                }
                            })
                            .catch(() => showAlert('Error de conexión.', 'danger'))
                            .finally(() => btn.disabled = false);
                    });
                })();

            })();
        </script>
    </x-slot>

</x-layouts.app>