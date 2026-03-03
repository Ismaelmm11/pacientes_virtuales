{{--
|--------------------------------------------------------------------------
| Previsualización del Prompt Generado
|--------------------------------------------------------------------------
|
| Muestra el prompt generado para un paciente en vista formateada (Markdown)
| o en código fuente. Permite copiar el prompt y publicar el paciente.
|
--}}
<x-layouts.app title="Previsualizar - {{ $patient->case_title }}">
    <x-slot:styles>
        <link href="{{ asset('css/patients.css') }}" rel="stylesheet">
    </x-slot:styles>

    <x-navbar backRoute="patients.index" backLabel="Volver a Mis Pacientes" rightLabel="Previsualización" />

    <div class="container" style="margin-top: 30px;">
        @if(session('success'))
            <div class="alert alert-success">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14" />
                    <polyline points="22 4 12 14.01 9 11.01" />
                </svg>
                {{ session('success') }}
            </div>
        @endif

        <div class="header-card">
            <h1>📋 {{ $patient->case_title }}</h1>
            <div class="header-meta">
                <span>🏥 {{ $patient->type->name ?? 'Sin tipo' }}</span>
                <span>📅 Creado: {{ $patient->created_at->format('d/m/Y H:i') }}</span>
                <span>📝 Versión: 1</span>
            </div>

            @if($patient->prompt && $patient->prompt->prompt_content)
                @php
                    $promptText = $patient->prompt->prompt_content;
                    $wordCount = str_word_count($promptText);
                    $charCount = strlen($promptText);
                @endphp
                <div class="stats-row">
                    <div class="stat">
                        <div class="stat-value">{{ number_format($wordCount) }}</div>
                        <div class="stat-label">Palabras</div>
                    </div>
                    <div class="stat">
                        <div class="stat-value">{{ number_format($charCount) }}</div>
                        <div class="stat-label">Caracteres</div>
                    </div>
                    <div class="stat">
                        <div class="stat-value">~{{ number_format(ceil($charCount / 4)) }}</div>
                        <div class="stat-label">Tokens (aprox)</div>
                    </div>
                </div>
            @endif
        </div>

        <div class="alert alert-info">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="12" cy="12" r="10" />
                <line x1="12" y1="16" x2="12" y2="12" />
                <line x1="12" y1="8" x2="12.01" y2="8" />
            </svg>
            <div>
                <strong>Revisa el prompt generado.</strong>
                Puedes copiarlo y pegarlo directamente en ChatGPT, Claude, Gemini u otro modelo de IA.
            </div>
        </div>

        <div class="view-toggle">
            <button class="view-btn active" onclick="showView('rendered')">👁️ Vista Formateada</button>
            <button class="view-btn" onclick="showView('source')">📝 Código Markdown</button>
        </div>

        <div class="prompt-container">
            <div class="prompt-header">
                <h2>
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" />
                        <polyline points="14 2 14 8 20 8" />
                    </svg>
                    Prompt del Paciente
                </h2>
                <div class="header-actions">
                    <button class="btn-copy" onclick="copyPrompt()" id="copyBtn">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2">
                            <rect x="9" y="9" width="13" height="13" rx="2" ry="2" />
                            <path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1" />
                        </svg>
                        <span>Copiar Prompt</span>
                    </button>
                </div>
            </div>

            <div class="prompt-body">
                @if($patient->prompt && $patient->prompt->prompt_content)
                    <div class="prompt-rendered" id="promptRendered"></div>
                    <pre class="prompt-source" id="promptSource">{{ $patient->prompt->prompt_content }}</pre>
                @else
                    <p style="text-align: center; color: var(--color-text-muted); padding: 40px;">
                        No hay prompt generado para este paciente.
                    </p>
                @endif
            </div>
        </div>

        <div class="actions-card">
            <div class="actions-info">
                <strong>¿Todo listo?</strong><br>
                Prueba la simulación antes de publicar, o copia el prompt para usarlo directamente.
            </div>
            <div class="actions-buttons">

                {{-- Probar simulación con selector de IA --}}
                @php
                    $aiProvidersConfig = config('ai.providers');
                    $aiNames = [
                        'openai' => 'ChatGPT',
                        'claude' => 'Claude',
                        'gemini' => 'Gemini',
                        'mistral' => 'Mistral',
                        'grok' => 'Grok',
                    ];
                @endphp

                <div style="display: flex; align-items: center; gap: 8px;">
                    <select id="testAiSelect"
                        style="padding: 10px 14px; border: 2px solid #dce1e6; border-radius: 8px; font-size: 14px; background: white; cursor: pointer;">
                        @foreach($aiNames as $key => $name)
                            @if(isset($aiProvidersConfig[$key]))
                                <option value="{{ $key }}">{{ $name }}</option>
                            @endif
                        @endforeach
                    </select>
                    <a href="#" id="testSimulationBtn" class="btn-large btn-secondary-large" onclick="goToTest(event)">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="20"
                            height="20">
                            <polygon points="5 3 19 12 5 21 5 3" />
                        </svg>
                        Probar Simulación
                    </a>
                </div>

                <a href="{{ route('patients.create') }}" class="btn-large btn-secondary-large">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <line x1="12" y1="5" x2="12" y2="19" />
                        <line x1="5" y1="12" x2="19" y2="12" />
                    </svg>
                    Crear Otro Paciente
                </a>

                @if(!$patient->is_published)
                    <form action="{{ route('patients.publish', $patient) }}" method="POST" style="display: inline;">
                        @csrf
                        <button type="submit" class="btn-large btn-primary-large">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14" />
                                <polyline points="22 4 12 14.01 9 11.01" />
                            </svg>
                            Publicar Paciente
                        </button>
                    </form>
                @else
                    <span class="btn-large" style="background: var(--color-success); color: white; cursor: default;">
                        ✓ Paciente Publicado
                    </span>
                @endif
            </div>
        </div>
    </div>
    <script>
        function goToTest(e) {
            e.preventDefault();
            const aiModel = document.getElementById('testAiSelect').value;
            const patientId = {{ $patient->id }};
            window.location.href = `/simulacion/${aiModel}/${patientId}`;
        }
    </script>

    <x-slot:scripts>
        <script src="https://cdn.jsdelivr.net/npm/marked/marked.min.js"></script>
        <script src="{{ asset('js/patient-preview.js') }}"></script>
    </x-slot:scripts>
</x-layouts.app>