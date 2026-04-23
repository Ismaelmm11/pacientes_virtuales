<x-layouts.app>

    <x-slot name="title">Admin — Pacientes</x-slot>
    <x-slot name="styles">
        <link href="{{ asset('css/dashboard.css') }}" rel="stylesheet">
        <link href="{{ asset('css/admin.css') }}" rel="stylesheet">
    </x-slot>

    <x-slot name="topbar">
        <div class="topbar">
            <div class="topbar-left">
                <div class="topbar-title">Supervisión de Pacientes</div>
                <div class="topbar-subtitle">Todos los pacientes virtuales de la plataforma</div>
            </div>
            <div class="topbar-right">
                <a href="{{ route('teacher.patients.create') }}?origen=admin" class="btn btn-primary btn-sm">
                    <i data-lucide="plus"></i>
                    Nuevo Paciente
                </a>
            </div>

        </div>
    </x-slot>

    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-card-icon primary"><i data-lucide="user-round"></i></div>
            <div class="stat-card-info">
                <div class="stat-card-value">{{ $totalPatients }}</div>
                <div class="stat-card-label">Total pacientes</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-card-icon secondary"><i data-lucide="eye"></i></div>
            <div class="stat-card-info">
                <div class="stat-card-value">{{ $totalPublished }}</div>
                <div class="stat-card-label">Publicados</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-card-icon warning"><i data-lucide="clock"></i></div>
            <div class="stat-card-info">
                <div class="stat-card-value">{{ $totalDraft }}</div>
                <div class="stat-card-label">Borradores</div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-body-form">
            <div class="admin-filters">

                {{-- Tabs de estado --}}
                <div class="admin-filter-tabs">
                    <button type="button" class="admin-filter-tab {{ !request('estado') ? 'active' : '' }}"
                        data-estado-filter="">Todos</button>
                    <button type="button"
                        class="admin-filter-tab {{ request('estado') === 'publicado' ? 'active' : '' }}"
                        data-estado-filter="publicado">Publicados</button>
                    <button type="button"
                        class="admin-filter-tab {{ request('estado') === 'borrador' ? 'active' : '' }}"
                        data-estado-filter="borrador">Borradores</button>
                </div>

                {{-- Tabs de modo --}}
                <div class="admin-filter-tabs">
                    <button type="button" class="admin-filter-tab {{ !request('modo') ? 'active' : '' }}"
                        data-modo-filter="">Todos</button>
                    <button type="button" class="admin-filter-tab {{ request('modo') === 'basic' ? 'active' : '' }}"
                        data-modo-filter="basic">Básico</button>
                    <button type="button" class="admin-filter-tab {{ request('modo') === 'advanced' ? 'active' : '' }}"
                        data-modo-filter="advanced">Avanzado</button>
                </div>

                <div class="admin-filters-right">
                    <div class="admin-per-page">
                        <span>Ver</span>
                        <select id="per-page-select">
                            @foreach([10, 20, 50, 100] as $opt)
                                <option value="{{ $opt }}" {{ $perPage == $opt ? 'selected' : '' }}>{{ $opt }}</option>
                            @endforeach
                        </select>
                        <span>por página</span>
                    </div>
                    <div class="admin-filter-search">
                        <i data-lucide="search"></i>
                        <input type="text" id="search-input" value="{{ request('buscar') }}"
                            placeholder="Buscar por título o descripción..." class="admin-search-input"
                            autocomplete="off">
                        <button type="button" id="search-clear"
                            class="admin-search-clear {{ request('buscar') ? '' : 'hidden' }}">
                            <i data-lucide="x"></i>
                        </button>
                    </div>
                </div>

            </div>
        </div>

        <div id="patients-table-container">
            @include('pages.admin.patients._table', ['patients' => $patients, 'perPage' => $perPage])
        </div>
    </div>

    <input type="hidden" id="estado-input" value="{{ request('estado', '') }}">
    <input type="hidden" id="modo-input" value="{{ request('modo', '') }}">

    <x-slot name="scripts">
        <script>
            (function () {
                const BASE_URL = '{{ route('admin.patients.index') }}';
                const container = document.getElementById('patients-table-container');
                const searchInput = document.getElementById('search-input');
                const searchClear = document.getElementById('search-clear');
                const perPageSelect = document.getElementById('per-page-select');
                const estadoInput = document.getElementById('estado-input');
                const modoInput = document.getElementById('modo-input');

                function buildUrl(overrides = {}) {
                    const params = new URLSearchParams();
                    const buscar = searchInput.value.trim();
                    const estado = estadoInput.value;
                    const modo = modoInput.value;
                    const perPage = perPageSelect.value;
                    if (buscar) params.set('buscar', buscar);
                    if (estado) params.set('estado', estado);
                    if (modo) params.set('modo', modo);
                    params.set('per_page', perPage);
                    Object.entries(overrides).forEach(([k, v]) => {
                        if (v === null || v === '') params.delete(k); else params.set(k, v);
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

                document.querySelectorAll('[data-estado-filter]').forEach(tab => {
                    tab.addEventListener('click', function () {
                        estadoInput.value = this.dataset.estadoFilter;
                        document.querySelectorAll('[data-estado-filter]').forEach(t => t.classList.remove('active'));
                        this.classList.add('active');
                        loadTable(buildUrl({ page: null }));
                    });
                });

                document.querySelectorAll('[data-modo-filter]').forEach(tab => {
                    tab.addEventListener('click', function () {
                        modoInput.value = this.dataset.modoFilter;
                        document.querySelectorAll('[data-modo-filter]').forEach(t => t.classList.remove('active'));
                        this.classList.add('active');
                        loadTable(buildUrl({ page: null }));
                    });
                });

                container.addEventListener('click', function (e) {
                    const link = e.target.closest('a.pagination-btn');
                    if (!link) return;
                    e.preventDefault();
                    loadTable(link.href);
                });
            })();
        </script>
    </x-slot>

</x-layouts.app>