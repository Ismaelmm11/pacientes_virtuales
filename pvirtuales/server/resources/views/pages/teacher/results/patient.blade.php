<x-layouts.app>

    <x-slot name="title">Resultados — {{ $patient->case_title }}</x-slot>

    <x-slot name="styles">
        <link href="{{ asset('css/dashboard.css') }}" rel="stylesheet">
        <link href="{{ asset('css/patients.css') }}" rel="stylesheet">
        <link href="{{ asset('css/results-show.css') }}" rel="stylesheet">
    </x-slot>

    <x-slot name="topbar">
        <div class="topbar">
            <div class="topbar-left">
                <div class="topbar-title">{{ $patient->case_title }}</div>
                <div class="topbar-subtitle">Resultados del examen</div>
            </div>
            <div class="topbar-right">
                <a href="{{ route('teacher.results.index') }}" class="btn btn-ghost btn-sm">
                    <i data-lucide="arrow-left"></i>
                    Volver
                </a>

                @if($patient->results_published)
                    {{-- Resultados ya publicados: mostrar estado + botón de despublicar --}}
                    <span class="badge badge-success">Resultados publicados</span>

                    <form method="POST" action="{{ route('teacher.patients.unpublish-results', $patient) }}">
                        @csrf
                        <button type="submit" class="btn btn-danger btn-sm">
                            <i data-lucide="eye-off"></i>
                            Despublicar
                        </button>
                    </form>

                @else
                    {{-- Resultados no publicados: botón publicar (deshabilitado si hay pendientes) --}}
                    @php $pendingCount = $attempts->whereNull('final_score')->count(); @endphp

                    <form method="POST" action="{{ route('teacher.patients.publish-results', $patient) }}">
                        @csrf
                        <button type="submit" class="btn btn-primary btn-sm" {{ $pendingCount > 0 ? 'disabled' : '' }}
                            title="{{ $pendingCount > 0 ? "Hay {$pendingCount} test(s) sin corregir" : 'Publicar resultados' }}">
                            <i data-lucide="send"></i>
                            Publicar resultados
                        </button>
                    </form>
                @endif
            </div>
        </div>
    </x-slot>

    {{-- ===== TARJETAS DE STATS ===== --}}
    <div class="stats-grid">

        {{-- Círculo de progreso: entregas --}}
        <div class="stat-card">
            <div class="stat-card-icon-wrap">
                <div class="progress-ring" style="--pct: {{ $deliveryRatio }}">
                    <div class="progress-ring-inner"></div>
                </div>
            </div>
            <div class="stat-card-info">
                <div class="stat-card-value">{{ $submittedCount }} / {{ $enrolledCount }}</div>
                <div class="stat-card-label">Alumnos entregados</div>
            </div>
        </div>

        {{-- Tiempo medio --}}
        <div class="stat-card">
            <div class="stat-card-icon secondary"><i data-lucide="clock"></i></div>
            <div class="stat-card-info">
                <div class="stat-card-value">{{ $avgDurationFormatted }}</div>
                <div class="stat-card-label">Tiempo medio consulta</div>
            </div>
        </div>

        {{-- Nota media --}}
        <div class="stat-card">
            <div class="stat-card-icon primary"><i data-lucide="bar-chart-2"></i></div>
            <div class="stat-card-info">
                <div class="stat-card-value">
                    {{ $avgGrade !== null ? number_format($avgGrade, 2) . ' / 10' : '—' }}
                </div>
                <div class="stat-card-label">Nota media</div>
            </div>
        </div>

        {{-- Tasa de aprobados --}}
        <div class="stat-card">
            <div class="stat-card-icon success"><i data-lucide="check-circle"></i></div>
            <div class="stat-card-info">
                <div class="stat-card-value">
                    @if($passCount !== null)
                        {{ $passCount }} / {{ $attempts->whereNotNull('final_score')->count() }}
                    @else
                        —
                    @endif
                </div>
                <div class="stat-card-label">Aprobados</div>
            </div>
        </div>

    </div>

    {{-- ===== TABS ===== --}}
    <div class="filter-tabs" id="filterTabs" style="margin: 24px 0 0;">
        <button class="filter-tab active" data-tab="submitted">
            Entregados
            <span class="filter-tab-count">{{ $attempts->count() }}</span>
        </button>
        <button class="filter-tab" data-tab="missing">
            Sin entregar
            <span class="filter-tab-count">{{ $studentsWithoutSubmission->count() }}</span>
        </button>
        {{-- Tab analíticas: solo visible cuando los resultados están publicados --}}
        @if($patient->results_published)
            <button class="filter-tab" data-tab="analytics">
                Analíticas
                <span class="filter-tab-count">6</span>
            </button>
        @endif
    </div>

    {{-- ===== TAB 1: ENTREGAS ===== --}}
    <div id="tab-submitted" class="card mt-lg">
        <div class="card-header">
            <div class="card-header-title">
                <i data-lucide="clipboard-check"></i>
                Alumnos que han entregado
            </div>
        </div>

        @if($attempts->isEmpty())
            <div class="empty-state">
                <div class="empty-state-icon"><i data-lucide="inbox"></i></div>
                <div class="empty-state-title">Sin entregas aún</div>
                <div class="empty-state-text">Ningún alumno ha enviado el test todavía.</div>
            </div>
        @else
            <div class="table-wrapper" style="border: none; border-radius: 0;">
                <table>
                    <thead>
                        <tr>
                            <th>Alumno</th>
                            <th>Tiempo consulta</th>
                            <th>Mensajes</th>
                            <th>Estado</th>
                            <th>Nota</th>
                            <th class="actions">Acción</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($attempts as $attempt)
                            <tr>
                                <td>
                                    <div class="patient-name">{{ $attempt->user?->full_name ?? '—' }}</div>
                                    <div class="patient-desc">{{ $attempt->user?->email ?? '' }}</div>
                                </td>
                                <td class="text-sm text-muted">
                                    {{ $attempt->duration_minutes !== null ? $attempt->duration_minutes . ' min' : '—' }}
                                </td>
                                <td class="text-sm">
                                    {{ $attempt->student_messages }}
                                </td>
                                <td>
                                    @if($attempt->final_score !== null)
                                        <span class="badge badge-success">Corregido</span>
                                    @else
                                        <span class="badge badge-warning">Pdte. corrección</span>
                                    @endif
                                </td>
                                <td>
                                    {{-- El profesor ve la nota siempre que esté corregida, independientemente de si están
                                    publicadas --}}
                                    @if($attempt->final_score !== null)
                                        @php $score = (float) $attempt->final_score; @endphp
                                        <span class="badge {{ $score >= 5 ? 'badge-success' : 'badge-danger' }}">
                                            {{ number_format($score, 2) }} / 10
                                        </span>
                                    @else
                                        <span class="text-muted text-sm">—</span>
                                    @endif
                                </td>

                                <td class="actions">
                                    {{-- Enlace al detalle del intento (próximamente) --}}
                                    <a href="{{ route('teacher.results.show', $attempt) }}" class="btn-action"
                                        title="Ver consulta">
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

    {{-- ===== TAB 2: SIN ENTREGAR ===== --}}
    <div id="tab-missing" class="card mt-lg" style="display: none;">
        <div class="card-header">
            <div class="card-header-title">
                <i data-lucide="user-x"></i>
                Alumnos sin entregar
            </div>
        </div>

        @if($studentsWithoutSubmission->isEmpty())
            <div class="empty-state">
                <div class="empty-state-icon"><i data-lucide="check-circle"></i></div>
                <div class="empty-state-title">Todos los alumnos han entregado</div>
            </div>
        @else
            <div class="table-wrapper" style="border: none; border-radius: 0;">
                <table>
                    <thead>
                        <tr>
                            <th>Alumno</th>
                            <th>Email</th>
                            {{-- Solo cuando los resultados están publicados mostramos la nota implícita --}}
                            @if($patient->results_published)
                                <th>Nota</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($studentsWithoutSubmission as $student)
                            <tr>
                                <td>
                                    <div class="patient-name">{{ $student->full_name }}</div>
                                </td>
                                <td class="text-sm text-muted">{{ $student->email }}</td>
                                {{-- Nota implícita 0: visual, no hay intento en BD --}}
                                @if($patient->results_published)
                                    <td>
                                        <span class="badge badge-danger">0 / 10</span>
                                    </td>
                                @endif
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    {{-- ===== TAB 3: ANALÍTICAS (solo cuando resultados publicados) ===== --}}
    @if($patient->results_published)
        <div id="tab-analytics" style="display: none;" class="card mt-lg">

            {{-- Botones para cambiar entre gráficas --}}
            <div class="chart-nav">
                <button class="chart-pill active">Notas por alumno</button>
                <button class="chart-pill">Distribución</button>
                <button class="chart-pill">Error por pregunta</button>
                <button class="chart-pill">Tiempo</button>
                <button class="chart-pill">Mensajes vs Nota</button>
                <button class="chart-pill">Heatmap</button>
            </div>

            {{-- Título y descripción dinámica de la gráfica activa --}}
            <div class="chart-meta">
                <div class="chart-meta-title" id="chart-meta-title">Nota por alumno</div>
                <div class="chart-meta-desc" id="chart-meta-desc">
                    Nota final de cada alumno que entregó el examen. Verde ≥ 5, rojo &lt; 5.
                </div>
            </div>

            {{-- Aquí se monta ApexCharts --}}
            <div id="analytics-chart"></div>

        </div>
    @endif


    <x-slot name="scripts">
        {{-- ApexCharts: solo se carga en esta página --}}
        <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

        <script>
            (function () {

                // ── Switching de tabs ─────────────────────────────────────────
                const tabs = document.querySelectorAll('.filter-tab');
                const tabSub = document.getElementById('tab-submitted');
                const tabMiss = document.getElementById('tab-missing');
                const tabAnalytics = document.getElementById('tab-analytics');
                let analyticsReady = false; // Flag para inicializar la gráfica solo la primera vez

                tabs.forEach(function (tab) {
                    tab.addEventListener('click', function () {
                        // Actualizar clase activa
                        tabs.forEach(function (t) { t.classList.remove('active'); });
                        this.classList.add('active');

                        // Ocultar todos los paneles
                        tabSub.style.display = 'none';
                        tabMiss.style.display = 'none';
                        if (tabAnalytics) tabAnalytics.style.display = 'none';

                        // Mostrar el panel correspondiente
                        if (this.dataset.tab === 'submitted') {
                            tabSub.style.display = '';
                        } else if (this.dataset.tab === 'missing') {
                            tabMiss.style.display = '';
                        } else if (this.dataset.tab === 'analytics' && tabAnalytics) {
                            tabAnalytics.style.display = '';
                            // Renderizar solo la primera vez que se abre el tab
                            if (!analyticsReady) {
                                analyticsReady = true;
                                renderChart(0);
                            }
                        }
                    });
                });

                // ── Analíticas con ApexCharts ─────────────────────────────────
                // Si el tab no existe (resultados no publicados) terminamos aquí
                if (!tabAnalytics) return;

                // Datos calculados en PHP, pasados como JSON
                const gradesData = @json($chartGrades);
                const distData = @json($chartDistribution);
                const errorsData = @json($chartQuestionErrors);
                const timesData = @json($chartTimes);
                const scatterData = @json($chartScatter);
                const heatmapData = @json($chartHeatmap);
                const heatmapLabels = @json($heatmapLabels);


                // Metadatos de cada gráfica
                const chartsMeta = [
                    {
                        title: 'Nota por alumno',
                        desc: 'Nota final de cada alumno que entregó el examen. Verde ≥ 5, rojo < 5.',
                    },
                    {
                        title: 'Distribución de notas',
                        desc: 'Cuántos alumnos obtuvieron cada rango de nota.',
                    },
                    {
                        title: 'Error por pregunta',
                        desc: 'Porcentaje de alumnos que respondieron incorrectamente cada pregunta, ordenado de mayor a menor.',
                    },
                    {
                        title: 'Tiempo de consulta',
                        desc: 'Duración en minutos de la consulta de cada alumno con el paciente virtual.',
                    },
                    {
                        title: 'Mensajes vs Nota',
                        desc: 'Relación entre el número de mensajes enviados al paciente y la nota final obtenida.',
                    },
                    {
                        title: 'Heatmap acierto / fallo',
                        desc: 'Verde = correcto, rojo = incorrecto. Filas = preguntas, columnas = alumnos.',
                    },
                ];

                let currentChart = null;
                const chartContainer = document.getElementById('analytics-chart');
                const titleEl = document.getElementById('chart-meta-title');
                const descEl = document.getElementById('chart-meta-desc');
                const pills = document.querySelectorAll('.chart-pill');

                // Construye las opciones de ApexCharts según el índice de la gráfica
                function buildOptions(index) {
                    // Opciones comunes a todas las gráficas
                    var base = {
                        chart: { toolbar: { show: false } },
                        grid: { borderColor: '#f1f1f1' },
                        tooltip: { theme: 'light' },
                    };

                    switch (index) {

                        // 0 — Nota por alumno (barras con color por alumno)
                        case 0:
                            return Object.assign({}, base, {
                                chart: Object.assign({}, base.chart, { type: 'bar', height: 380 }),
                                series: [{
                                    name: 'Nota',
                                    data: gradesData.map(function (d) {
                                        return {
                                            x: d.name,
                                            y: d.score,
                                            fillColor: d.score >= 5 ? '#16a34a' : '#dc2626',
                                        };
                                    }),
                                }],
                                yaxis: { min: 0, max: 10, title: { text: 'Nota / 10' } },
                                plotOptions: { bar: { columnWidth: '55%', borderRadius: 4 } },
                                dataLabels: { enabled: false },
                                tooltip: { y: { formatter: function (v) { return v.toFixed(2) + ' / 10'; } } },
                            });

                        // 1 — Distribución de notas (rangos)
                        case 1:
                            return Object.assign({}, base, {
                                chart: Object.assign({}, base.chart, { type: 'bar', height: 350 }),
                                series: [{ name: 'Alumnos', data: Object.values(distData) }],
                                xaxis: { categories: Object.keys(distData), title: { text: 'Rango de nota' } },
                                yaxis: { title: { text: 'Nº de alumnos' }, min: 0 },
                                colors: ['#6366f1'],
                                plotOptions: { bar: { columnWidth: '55%', borderRadius: 4 } },
                                dataLabels: { enabled: true },
                                tooltip: { y: { formatter: function (v) { return v + ' alumno(s)'; } } },
                            });

                        // 2 — Error por pregunta (barra horizontal)
                        case 2:
                            return Object.assign({}, base, {
                                chart: Object.assign({}, base.chart, {
                                    type: 'bar',
                                    height: Math.max(300, errorsData.length * 48),
                                }),
                                series: [{ name: '% de fallos', data: errorsData.map(function (d) { return d.pct; }) }],
                                xaxis: { categories: errorsData.map(function (d) { return d.text; }), max: 100 },
                                yaxis: { title: { text: '% de fallos' } },
                                colors: ['#dc2626'],
                                plotOptions: { bar: { horizontal: true, borderRadius: 4 } },
                                dataLabels: { enabled: true, formatter: function (v) { return v + '%'; } },
                                tooltip: { y: { formatter: function (v) { return v + '% de alumnos fallaron esta pregunta'; } } },
                            });

                        // 3 — Tiempo por alumno
                        case 3:
                            return Object.assign({}, base, {
                                chart: Object.assign({}, base.chart, { type: 'bar', height: 380 }),
                                series: [{
                                    name: 'Minutos',
                                    data: timesData.map(function (d) { return { x: d.name, y: d.minutes }; }),
                                }],
                                yaxis: { title: { text: 'Minutos' }, min: 0 },
                                colors: ['#6366f1'],
                                plotOptions: { bar: { columnWidth: '55%', borderRadius: 4 } },
                                dataLabels: { enabled: false },
                                tooltip: { y: { formatter: function (v) { return v + ' min'; } } },
                            });

                        // 4 — Scatter mensajes vs nota
                        case 4:
                            return Object.assign({}, base, {
                                chart: Object.assign({}, base.chart, {
                                    type: 'scatter',
                                    height: 380,
                                    zoom: { enabled: false }, // ← sin zoom
                                    selection: { enabled: false }, // ← sin selección/arrastre
                                }),
                                series: [{
                                    name: 'Alumnos',
                                    data: scatterData.map(function (d) { return { x: d.x, y: d.y }; }),
                                }],
                                xaxis: { title: { text: 'Mensajes enviados' }, min: 0, tickAmount: 5 },
                                yaxis: { title: { text: 'Nota final' }, min: 0, max: 10 },
                                colors: ['#6366f1'],
                                markers: { size: 8, hover: { size: 11 } },
                                tooltip: {
                                    custom: function (opts) {
                                        var d = scatterData[opts.dataPointIndex];
                                        return '<div style="padding:10px 14px;font-size:0.85rem;">'
                                            + '<strong>' + d.name + '</strong><br>'
                                            + 'Mensajes: ' + d.x + '<br>'
                                            + 'Nota: ' + d.y.toFixed(2)
                                            + '</div>';
                                    },
                                },
                            });

                        // 5 — Heatmap pregunta × alumno con tooltip personalizado
                        case 5:
                            return Object.assign({}, base, {
                                chart: Object.assign({}, base.chart, {
                                    type: 'heatmap',
                                    height: Math.max(250, heatmapData.length * 55),
                                }),
                                series: heatmapData,
                                yaxis: {reversed: true}, // ocultamos etiquetas Y (preguntas) porque ya las tenemos en el tooltip
                                dataLabels: { enabled: false },
                                plotOptions: {
                                    heatmap: {
                                        colorScale: {
                                            ranges: [
                                                { from: 0, to: 0, color: '#dc2626', name: 'Incorrecto' },
                                                { from: 1, to: 1, color: '#16a34a', name: 'Correcto' },
                                            ],
                                        },
                                    },
                                },
                                // Tooltip personalizado: muestra el texto completo de la pregunta + resultado
                                tooltip: {
                                    custom: function (opts) {
                                        var si = opts.seriesIndex;      // índice de la pregunta (fila)
                                        var di = opts.dataPointIndex;   // índice del alumno (columna)
                                        var value = opts.series[si][di];
                                        var alumno = heatmapData[si].data[di].x;
                                        var fullText = heatmapLabels[si];
                                        var resultado = value === 1 ? 'Correcto' : 'Incorrecto';
                                        var color = value === 1 ? '#16a34a' : '#dc2626';

                                        return '<div style="padding:10px 14px;font-size:0.85rem;max-width:280px;">'
                                            + '<strong>Pregunta ' + (si + 1) + '</strong><br>'
                                            + '<span style="color:#6b7280;display:block;margin:4px 0 8px;">'
                                            + fullText
                                            + '</span>'
                                            + '<strong>' + alumno + ':</strong> '
                                            + '<span style="color:' + color + ';font-weight:600;">' + resultado + '</span>'
                                            + '</div>';
                                    },
                                },
                            });
                        default:
                            return base;
                    }
                }

                // Renderiza la gráfica del índice dado
                function renderChart(index) {
                    // Actualizar pill activo
                    pills.forEach(function (p, i) {
                        p.classList.toggle('active', i === index);
                    });

                    // Actualizar título y descripción
                    titleEl.textContent = chartsMeta[index].title;
                    descEl.textContent = chartsMeta[index].desc;

                    // Destruir gráfica anterior
                    if (currentChart) {
                        currentChart.destroy();
                        currentChart = null;
                    }

                    // Crear y montar nueva gráfica
                    currentChart = new ApexCharts(chartContainer, buildOptions(index));
                    currentChart.render();
                }

                // Asignar click a cada pill
                pills.forEach(function (pill, i) {
                    pill.addEventListener('click', function () { renderChart(i); });
                });

            })();
        </script>
    </x-slot>


</x-layouts.app>