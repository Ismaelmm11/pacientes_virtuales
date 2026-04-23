<x-layouts.app>

    <x-slot name="title">Admin — Asignaturas</x-slot>
    <x-slot name="styles">
        <link href="{{ asset('css/dashboard.css') }}" rel="stylesheet">
        <link href="{{ asset('css/admin.css') }}" rel="stylesheet">
    </x-slot>

    <x-slot name="topbar">
        <div class="topbar">
            <div class="topbar-left">
                <div class="topbar-title">Gestión de Asignaturas</div>
                <div class="topbar-subtitle">Todas las asignaturas de la plataforma</div>
            </div>
            <div class="topbar-right">
                <button type="button" class="btn btn-primary btn-sm" id="btn-new-subject">
                    <i data-lucide="plus"></i>
                    Nueva asignatura
                </button>
            </div>
        </div>
    </x-slot>


    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-card-icon primary"><i data-lucide="book-open"></i></div>
            <div class="stat-card-info">
                <div class="stat-card-value">{{ $totalSubjects }}</div>
                <div class="stat-card-label">Asignaturas</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-card-icon secondary"><i data-lucide="users"></i></div>
            <div class="stat-card-info">
                <div class="stat-card-value">{{ $totalEnrollments }}</div>
                <div class="stat-card-label">Matriculaciones</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-card-icon warning"><i data-lucide="user-round"></i></div>
            <div class="stat-card-info">
                <div class="stat-card-value">{{ $totalPatients }}</div>
                <div class="stat-card-label">Pacientes virtuales</div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-body-form">
            <div class="admin-filters">
                <div class="admin-filter-search">
                    <i data-lucide="search"></i>
                    <input type="text" id="search-input" value="{{ request('buscar') }}"
                        placeholder="Buscar por nombre, código o institución..." class="admin-search-input"
                        autocomplete="off">
                    <button type="button" id="search-clear"
                        class="admin-search-clear {{ request('buscar') ? '' : 'hidden' }}">
                        <i data-lucide="x"></i>
                    </button>
                </div>

                <div class="admin-per-page">
                    <span>Ver</span>
                    <select id="per-page-select">
                        @foreach([10, 20, 50, 100] as $opt)
                            <option value="{{ $opt }}" {{ $perPage == $opt ? 'selected' : '' }}>{{ $opt }}</option>
                        @endforeach
                    </select>
                    <span>por página</span>
                </div>
            </div>
        </div>

        <div id="subjects-table-container">
            @include('pages.admin.subjects._table', ['subjects' => $subjects, 'perPage' => $perPage])
        </div>
    </div>

    <div class="admin-modal-overlay" id="modal-create-subject">
        <div class="admin-modal">

            <div class="admin-modal-header">
                <div class="admin-modal-title">
                    <i data-lucide="book-plus"></i>
                    Nueva asignatura
                </div>
                <button type="button" class="admin-modal-close" id="btn-close-create-modal">
                    <i data-lucide="x"></i>
                </button>
            </div>

            <form id="form-create-subject" novalidate>
                @csrf
                <div class="admin-modal-body">

                    <div id="create-modal-alert" class="alert" style="display:none;"></div>

                    <div class="admin-modal-row">
                        <div class="admin-modal-field">
                            <label class="form-hint" style="font-weight:600;color:var(--color-text);">Nombre</label>
                            <input type="text" name="name" class="form-input" placeholder="Ej: Urgencias Pediátricas"
                                required>
                        </div>
                        <div class="admin-modal-field">
                            <label class="form-hint" style="font-weight:600;color:var(--color-text);">Código</label>
                            <input type="text" name="code" class="form-input" placeholder="Ej: MED-101" required>
                        </div>
                    </div>

                    <div class="admin-modal-field mt-md">
                        <label class="form-hint" style="font-weight:600;color:var(--color-text);">Institución</label>
                        <input type="text" name="institution" class="form-input"
                            placeholder="Ej: Universidad de Sevilla" required>
                    </div>

                    <div class="admin-modal-field mt-md">
                        <label class="form-hint" style="font-weight:600;color:var(--color-text);">Profesor
                            responsable</label>
                        <select name="created_by_user_id" class="form-select">
                            @foreach($teachers as $teacher)
                                <option value="{{ $teacher->id }}">
                                    {{ $teacher->full_name }}{{ $teacher->isAdmin() ? ' (Admin)' : '' }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                </div>

                <div class="admin-modal-footer">
                    <button type="button" class="btn btn-ghost btn-sm" id="btn-cancel-create-modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary btn-sm" id="btn-save-new-subject">
                        <i data-lucide="plus"></i>
                        Crear asignatura
                    </button>
                </div>
            </form>

        </div>
    </div>


    <x-slot name="scripts">
        <script>
            (function () {
                const BASE_URL = '{{ route('admin.subjects.index') }}';
                const container = document.getElementById('subjects-table-container');
                const searchInput = document.getElementById('search-input');
                const searchClear = document.getElementById('search-clear');
                const perPageSelect = document.getElementById('per-page-select');

                function buildUrl(overrides = {}) {
                    const params = new URLSearchParams();
                    const buscar = searchInput.value.trim();
                    const perPage = perPageSelect.value;
                    if (buscar) params.set('buscar', buscar);
                    params.set('per_page', perPage);
                    Object.entries(overrides).forEach(([k, v]) => {
                        if (v === null) params.delete(k); else params.set(k, v);
                    });
                    return BASE_URL + '?' + params.toString();
                }

                function loadTable(url) {
                    container.classList.add('table-loading');
                    fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                        .then(r => r.text())
                        .then(html => {
                            container.innerHTML = html;
                            history.pushState({}, '', url);
                            lucide.createIcons();
                            container.classList.remove('table-loading');
                        })
                        .catch(() => container.classList.remove('table-loading'));
                }

                let searchTimer;
                searchInput.addEventListener('input', function () {
                    clearTimeout(searchTimer);
                    searchClear.classList.toggle('hidden', !this.value.trim());
                    searchTimer = setTimeout(() => loadTable(buildUrl({ page: null })), 350);
                });

                searchClear.addEventListener('click', function () {
                    searchInput.value = '';
                    this.classList.add('hidden');
                    loadTable(buildUrl({ page: null }));
                });

                perPageSelect.addEventListener('change', () => loadTable(buildUrl({ page: null })));

                container.addEventListener('click', function (e) {
                    const link = e.target.closest('a.pagination-btn');
                    if (!link) return;
                    e.preventDefault();
                    loadTable(link.href);
                });

                // ---- Modal de creación ----
                (function () {
                    const overlay = document.getElementById('modal-create-subject');
                    const btnNew = document.getElementById('btn-new-subject');
                    const btnClose = document.getElementById('btn-close-create-modal');
                    const btnCancel = document.getElementById('btn-cancel-create-modal');
                    const form = document.getElementById('form-create-subject');
                    const alertEl = document.getElementById('create-modal-alert');
                    const STORE_URL = '{{ route('admin.subjects.store') }}';

                    function openModal() { form.reset(); hideAlert(); overlay.classList.add('active'); lucide.createIcons(); }
                    function closeModal() { overlay.classList.remove('active'); }
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
                        const btn = document.getElementById('btn-save-new-subject');
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
                                    showAlert('Asignatura creada correctamente.');
                                    setTimeout(() => {
                                        closeModal();
                                        loadTable(buildUrl());
                                    }, 900);
                                } else {
                                    const errors = res.errors
                                        ? Object.values(res.errors).flat().join(' · ')
                                        : 'Error al crear la asignatura.';
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