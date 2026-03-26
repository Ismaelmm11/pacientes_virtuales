{{--
|--------------------------------------------------------------------------
| Listado de Pacientes — Profesor
|--------------------------------------------------------------------------
|
| Muestra todos los pacientes creados por el profesor autenticado.
| Incluye filtros por estado y modo, búsqueda por nombre,
| y acciones completas por paciente.
|
| DATOS RECIBIDOS:
|   $patients → Collection de Patient (con subject cargado)
|
--}}

<x-layouts.app>

    {{-- Título de la pestaña --}}

    <x-slot name="title">Mis Pacientes</x-slot>

    <x-slot name="styles">
        <link href="{{ asset('css/patients.css') }}" rel="stylesheet">
    </x-slot>

    {{-- Topbar --}}
    <x-slot name="topbar">
        <div class="topbar">
            <div class="topbar-left">
                <div class="topbar-title">Mis Pacientes</div>
                <div class="topbar-subtitle">
                    {{ $patients->count() }} {{ $patients->count() === 1 ? 'paciente creado' : 'pacientes creados' }}
                </div>
            </div>
            <div class="topbar-right">
                <a href="{{ route('teacher.patients.create') }}" class="btn btn-primary btn-sm">
                    <i data-lucide="plus"></i>
                    Nuevo Paciente
                </a>
            </div>
        </div>
    </x-slot>

    {{-- ================================================================
         FILTROS Y BÚSQUEDA
         ================================================================ --}}
    <div class="patients-filters">

        {{-- Búsqueda por nombre --}}
        <div class="patients-search">
            <input type="text"
                   id="searchInput"
                   placeholder="Buscar paciente..."
                   class="patients-search-input">
        </div>

        {{-- Filtros por estado --}}
        <div class="filter-tabs" id="filterTabs">
            <button class="filter-tab active" data-filter="all">
                Todos
                <span class="filter-tab-count">{{ $patients->count() }}</span>
            </button>
            <button class="filter-tab" data-filter="published">
                Publicados
                <span class="filter-tab-count">{{ $patients->where('is_published', true)->count() }}</span>
            </button>
            <button class="filter-tab" data-filter="draft">
                Borradores
                <span class="filter-tab-count">{{ $patients->where('is_published', false)->count() }}</span>
            </button>
        </div>

    </div>

    {{-- ================================================================
         LISTADO
         ================================================================ --}}
    @if($patients->isEmpty())

        {{-- Estado vacío --}}
        <div class="card">
            <div class="empty-state">
                <div class="empty-state-icon">
                    <i data-lucide="user-round-x"></i>
                </div>
                <div class="empty-state-title">Aún no tienes pacientes</div>
                <div class="empty-state-text">
                    Crea tu primer paciente virtual para que tus alumnos puedan practicar entrevistas clínicas.
                </div>
                <a href="{{ route('teacher.patients.create') }}" class="btn btn-primary mt-md">
                    <i data-lucide="plus"></i>
                    Crear primer paciente
                </a>
            </div>
        </div>

    @else

        <div class="card">
            <div class="table-wrapper" style="border: none; border-radius: 0;">
                <table id="patientsTable">
                    <thead>
                        <tr>
                            <th>Paciente</th>
                            <th>Modo</th>
                            <th>Asignatura</th>
                            <th>Test</th>
                            <th>Estado</th>
                            <th>Creado</th>
                            <th class="actions">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($patients as $patient)
                            <tr class="patient-row"
                                data-status="{{ $patient->is_published ? 'published' : 'draft' }}"
                                data-name="{{ strtolower($patient->case_title) }}">

                                {{-- Nombre + descripción --}}
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

                                {{-- Asignatura --}}
                                <td>
                                    <span class="text-muted text-sm">
                                        {{ $patient->subject?->name ?? '—' }}
                                    </span>
                                </td>

                                {{-- Test: tiene preguntas o no --}}
                                <td>
                                    @if($patient->hasTest())
                                        <span class="badge badge-success">
                                            <i data-lucide="check"></i>
                                            Con test
                                        </span>
                                    @else
                                        <span class="badge badge-neutral">
                                            <i data-lucide="minus"></i>
                                            Sin test
                                        </span>
                                    @endif
                                </td>

                                {{-- Estado --}}
                                <td>
                                    @if($patient->is_published)
                                        <span class="badge badge-success">
                                            <i data-lucide="globe"></i>
                                            Publicado
                                        </span>
                                    @else
                                        <span class="badge badge-warning">
                                            <i data-lucide="clock"></i>
                                            Borrador
                                        </span>
                                    @endif
                                </td>

                                {{-- Fecha --}}
                                <td class="text-muted text-sm">
                                    {{ $patient->created_at->diffForHumans() }}
                                </td>

                                {{-- Acciones --}}
                                <td class="actions">
                                    <div class="row-actions">

                                        {{-- Ver prompt --}}
                                        <a href="{{ route('teacher.patients.preview', $patient) }}"
                                           class="btn-action"
                                           title="Previsualizar prompt">
                                            <i data-lucide="eye"></i>
                                        </a>

                                        {{-- Gestionar test --}}
                                        <a href="{{ route('teacher.patients.test', $patient) }}"
                                           class="btn-action"
                                           title="Gestionar test de evaluación">
                                            <i data-lucide="clipboard-list"></i>
                                        </a>

                                        {{-- Publicar (solo borradores) --}}
                                        @if(!$patient->is_published)
                                            <form action="{{ route('teacher.patients.publish', $patient) }}"
                                                  method="POST"
                                                  style="display:inline;">
                                                @csrf
                                                <button type="submit"
                                                        class="btn-action btn-action-success"
                                                        title="Publicar paciente">
                                                    <i data-lucide="send"></i>
                                                </button>
                                            </form>
                                        @endif

                                        {{-- Eliminar --}}
                                        <form action="{{ route('teacher.patients.destroy', [$patient, 'index']) }}"
                                              method="POST"
                                              style="display:inline;"
                                              onsubmit="return confirm('¿Eliminar {{ addslashes($patient->case_title) }}? Esta acción no se puede deshacer.')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                    class="btn-action btn-action-danger"
                                                    title="Eliminar paciente">
                                                <i data-lucide="trash-2"></i>
                                            </button>
                                        </form>

                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Mensaje cuando el filtro no encuentra resultados --}}
            <div class="patients-empty-filter" id="emptyFilter" style="display: none;">
                <div class="empty-state" style="padding: 40px;">
                    <div class="empty-state-icon">
                        <i data-lucide="search-x"></i>
                    </div>
                    <div class="empty-state-title">Sin resultados</div>
                    <div class="empty-state-text">
                        No hay pacientes que coincidan con los filtros aplicados.
                    </div>
                </div>
            </div>

        </div>

    @endif

    {{-- ================================================================
         JS: Filtros y búsqueda en cliente (sin recargar página)
         ================================================================ --}}
    <x-slot name="scripts">
        <script>
        (function () {

            const searchInput  = document.getElementById('searchInput');
            const filterTabs   = document.getElementById('filterTabs');
            const rows         = document.querySelectorAll('.patient-row');
            const emptyFilter  = document.getElementById('emptyFilter');

            let currentFilter = 'all';
            let currentSearch = '';

            // --- Aplica filtro + búsqueda ---
            function applyFilters() {
                let visible = 0;

                rows.forEach(row => {
                    const status = row.dataset.status;   // 'published' | 'draft'
                    const name   = row.dataset.name;     // nombre en minúsculas

                    const matchFilter = currentFilter === 'all' || status === currentFilter;
                    const matchSearch = name.includes(currentSearch);

                    if (matchFilter && matchSearch) {
                        row.style.display = '';
                        visible++;
                    } else {
                        row.style.display = 'none';
                    }
                });

                // Mostrar mensaje vacío si no hay filas visibles
                if (emptyFilter) {
                    emptyFilter.style.display = visible === 0 ? 'block' : 'none';
                }
            }

            // --- Tabs de filtro ---
            if (filterTabs) {
                filterTabs.querySelectorAll('.filter-tab').forEach(tab => {
                    tab.addEventListener('click', function () {
                        filterTabs.querySelectorAll('.filter-tab').forEach(t => t.classList.remove('active'));
                        this.classList.add('active');
                        currentFilter = this.dataset.filter;
                        applyFilters();
                    });
                });
            }

            // --- Input de búsqueda ---
            if (searchInput) {
                searchInput.addEventListener('input', function () {
                    currentSearch = this.value.toLowerCase().trim();
                    applyFilters();
                });
            }

        })();
        </script>
    </x-slot>

</x-layouts.app>