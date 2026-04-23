{{--
|--------------------------------------------------------------------------
| Previsualización del Prompt Generado
|--------------------------------------------------------------------------
--}}
<x-layouts.app>

    <x-slot name="title">Previsualizar — {{ $patient->case_title }}</x-slot>

    <x-slot name="styles">
        <link href="{{ asset('css/create-patient.css') }}" rel="stylesheet">
        <link href="{{ asset('css/dashboard.css') }}" rel="stylesheet">
    </x-slot>

    <x-slot name="topbar">
        <div class="topbar">
            <div class="topbar-left">
                <div class="topbar-title">{{ $patient->case_title }}</div>
                <div class="topbar-subtitle">
                    <span class="mode-badge">
                        <i data-lucide="{{ $patient->mode === 'basic' ? 'zap' : 'settings-2' }}"></i>
                        Modo {{ $patient->mode === 'basic' ? 'Básico' : 'Avanzado' }}
                    </span>
                </div>
            </div>
            <div class="topbar-right">
                @if(request('origen') === 'admin')
                    <a href="{{ route('admin.patients.index') }}" class="btn btn-ghost btn-sm">
                        <i data-lucide="arrow-left"></i>
                        Panel Admin
                    </a>
                @else
                    <a href="{{ route('teacher.patients.index') }}" class="btn btn-ghost btn-sm">
                        <i data-lucide="arrow-left"></i>
                        Mis Pacientes
                    </a>
                @endif

                @if(request('origen') !== 'admin')
                    <a href="{{ route('teacher.patients.create') }}" class="btn btn-ghost btn-sm">
                        <i data-lucide="plus"></i>
                        Crear Otro
                    </a>
                @endif

                @if(!$patient->is_published)
                    <form action="{{ route('teacher.patients.publish', $patient) }}" method="POST" class="cp-form-inline">
                        @csrf
                        <input type="hidden" name="origen" value="{{ request('origen') }}">
                        <button type="submit" class="btn btn-primary btn-sm">
                            <i data-lucide="send"></i>
                            Publicar Paciente
                        </button>
                    </form>
                @else
                    <form action="{{ route('teacher.patients.publish', $patient) }}" method="POST" class="cp-form-inline">
                        @csrf
                        <input type="hidden" name="origen" value="{{ request('origen') }}">
                        <button type="submit" class="btn btn-ghost btn-sm">
                            <i data-lucide="eye-off"></i>
                            Despublicar
                        </button>
                    </form>
                @endif

            </div>
        </div>
    </x-slot>

    <div class="create-patient-layout">

        {{-- ===== COLUMNA PRINCIPAL ===== --}}
        <div class="create-patient-main">

            {{-- Card: acciones principales --}}
            <div class="cp-actions-grid">

                <div class="cp-action-card">
                    <div class="cp-action-card-header">
                        <div class="cp-section-icon"><i data-lucide="play"></i></div>
                        <p class="cp-action-card-title">Probar Simulación</p>
                    </div>
                    <p class="cp-action-card-desc">Habla con el paciente antes de publicarlo para verificar su
                        comportamiento.</p>
                    <div class="cp-action-card-footer">
                        <select id="testAiSelect" class="form-control cp-action-select">
                            @php
                                $aiNames = ['openai' => 'ChatGPT', 'claude' => 'Claude', 'gemini' => 'Gemini', 'mistral' => 'Mistral', 'grok' => 'Grok'];
                                $aiProvidersConfig = config('ai.providers', []);
                            @endphp
                            @foreach($aiNames as $key => $name)
                                @if(isset($aiProvidersConfig[$key]))
                                    <option value="{{ $key }}">{{ $name }}</option>
                                @endif
                            @endforeach
                        </select>
                        <button onclick="goToTest()" class="btn btn-primary btn-sm">
                            <i data-lucide="play"></i>
                        </button>
                    </div>
                </div>

                <div class="cp-action-card">
                    <div class="cp-action-card-header">
                        <div class="cp-section-icon"><i data-lucide="clipboard-list"></i></div>
                        <p class="cp-action-card-title">Cuestionario</p>
                    </div>
                    <p class="cp-action-card-desc">Crea o edita las preguntas de evaluación para este caso clínico.</p>

                        poder publicar.</p>
                    <div class="cp-action-card-footer">
                        <a href="{{ route('teacher.patients.test', $patient) }}{{ request('origen') ? '?origen=' . request('origen') : '' }}"
                            class="btn btn-ghost btn-sm">
                            <i data-lucide="clipboard-list"></i>
                            Gestionar Preguntas
                        </a>
                    </div>
                </div>

                <div class="cp-action-card">
                    <div class="cp-action-card-header">
                        <div class="cp-section-icon"><i data-lucide="pencil"></i></div>
                        <p class="cp-action-card-title">Editar Paciente</p>
                    </div>
                    <p class="cp-action-card-desc">Modifica los datos del caso. El prompt se regenerará automáticamente
                        al guardar.</p>
                    <div class="cp-action-card-footer">
                        <a href="{{ route('teacher.patients.edit', $patient) }}{{ request('origen') ? '?origen=' . request('origen') : '' }}"
                            class="btn btn-ghost btn-sm">
                            <i data-lucide="pencil"></i>
                            Editar
                        </a>
                    </div>
                </div>

            </div>

            {{-- Card: métricas del prompt --}}
            @if($patient->prompt && $patient->prompt->prompt_content)
                @php
                    $promptText = $patient->prompt->prompt_content;
                    $wordCount = str_word_count($promptText);
                    $charCount = strlen($promptText);
                    $tokens = ceil($charCount / 4);
                @endphp

                <div class="cp-section">
                    <div class="cp-section-header">
                        <div class="cp-section-icon"><i data-lucide="sparkles"></i></div>
                        <h2 class="cp-section-title">Prompt Generado</h2>
                    </div>
                    <p class="cp-section-desc">El sistema ha generado automáticamente el prompt para la IA a partir de los
                        datos que introdujiste.</p>

                    {{-- Stats --}}
                    <div class="cp-prompt-stats">
                        @foreach([['Palabras', number_format($wordCount), 'file-text'], ['Caracteres', number_format($charCount), 'hash'], ['Tokens aprox.', '~' . number_format($tokens), 'cpu']] as [$label, $value, $icon])
                            <div class="cp-prompt-stat">
                                <div class="cp-prompt-stat-value">{{ $value }}</div>
                                <div class="cp-prompt-stat-label">{{ $label }}</div>
                            </div>
                        @endforeach
                    </div>

                    <div class="cp-prompt-toggle">
                        <button onclick="showView('rendered')" id="btnRendered" class="btn btn-ghost btn-sm">
                            <i data-lucide="eye"></i> Vista Formateada
                        </button>
                        <button onclick="showView('source')" id="btnSource" class="btn btn-ghost btn-sm">
                            <i data-lucide="code"></i> Código Markdown
                        </button>
                        <div class="cp-prompt-toggle-right">
                            <button onclick="copyPrompt()" class="btn btn-ghost btn-sm" id="copyBtn">
                                <i data-lucide="copy"></i> Copiar
                            </button>
                        </div>
                    </div>

                    <div class="cp-prompt-box">
                        <div id="promptRendered" class="cp-prompt-rendered cp-prompt-hidden">
                            {{ $patient->prompt->prompt_content }}
                        </div>
                        <pre id="promptSource"
                            class="cp-prompt-source cp-prompt-hidden">{{ $patient->prompt->prompt_content }}</pre>
                    </div>
                </div>
            @else
                <div class="cp-section">
                    <p class="p-salvaje">
                        No hay prompt generado para este paciente.
                    </p>
                </div>
            @endif

        </div>

        {{-- ===== SIDEBAR ===== --}}
        <aside class="create-patient-sidebar">

            {{-- Info del paciente --}}
            <div class="cp-sidebar-card">
                <div class="cp-sidebar-title">Detalles del Caso</div>
                <div class="cp-sidebar-details">
                    <div>
                        <div class="cp-sidebar-detail-label">Asignatura</div>
                        <div>{{ $patient->subject?->name ?? '—' }}</div>
                    </div>
                    <div>
                        <div class="cp-sidebar-detail-label">
                            Descripción</div>
                        <div>{{ $patient->patient_description ?? '—' }}</div>
                    </div>
                    <div>
                        <div class="cp-sidebar-detail-label">
                            Estado</div>
                        @if($patient->is_published)
                            <span class="badge badge-success"><i data-lucide="check"></i> Publicado</span>
                        @else
                            <span class="badge badge-warning"><i data-lucide="clock"></i> Borrador</span>
                        @endif
                    </div>
                    <div>
                        <div class="cp-sidebar-detail-label">
                            Creado</div>
                        <div>{{ $patient->created_at->format('d/m/Y H:i') }}</div>
                    </div>
                </div>
            </div>

            {{-- Pasos siguientes --}}
            <div class="cp-sidebar-card cp-sidebar-info">
                <div class="cp-sidebar-info-icon"><i data-lucide="list-checks"></i></div>
                <div class="cp-sidebar-info-title">Pasos Siguientes</div>
                <div class="cp-sidebar-info-text">
                    <ol class="cp-next-steps-list">
                        <li>Revisa el prompt generado</li>
                        <li>Prueba la simulación</li>
                        <li>Publica el paciente</li>
                    </ol>
                </div>
            </div>

        </aside>

    </div>

    <x-slot name="scripts">
        <script src="https://cdn.jsdelivr.net/npm/marked/marked.min.js"></script>
        <script>
            // Renderizar Markdown al cargar
            const rawPrompt = document.getElementById('promptSource')?.textContent ?? '';
            const rendered = document.getElementById('promptRendered');
            if (rendered && rawPrompt) {
                rendered.innerHTML = marked.parse(rawPrompt);
            }

            function showView(view) {
                document.getElementById('promptRendered').classList.toggle('cp-prompt-hidden', view !== 'rendered');
                document.getElementById('promptSource').classList.toggle('cp-prompt-hidden', view !== 'source');
                document.getElementById('btnRendered').className = 'btn btn-sm ' + (view === 'rendered' ? 'btn-primary' : 'btn-ghost');
                document.getElementById('btnSource').className = 'btn btn-sm ' + (view === 'source' ? 'btn-primary' : 'btn-ghost');
            }

            function copyPrompt() {
                navigator.clipboard.writeText(rawPrompt).then(() => {
                    const btn = document.getElementById('copyBtn');
                    btn.innerHTML = '<i data-lucide="check"></i> Copiado';
                    lucide.createIcons();
                    setTimeout(() => {
                        btn.innerHTML = '<i data-lucide="copy"></i> Copiar';
                        lucide.createIcons();
                    }, 2000);
                });
            }

            function goToTest() {
                const ai = document.getElementById('testAiSelect').value;
                window.location.href = `/simulacion/${ai}/{{ $patient->id }}`;
            }
        </script>
    </x-slot>

</x-layouts.app>