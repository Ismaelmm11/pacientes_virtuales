{{--
|--------------------------------------------------------------------------
| Gestión del Test de Evaluación del Paciente
|--------------------------------------------------------------------------
|
| Página dedicada donde el profesor crea las preguntas del test que
| el estudiante responderá tras la simulación.
|
| TIPOS SOPORTADOS:
| - Opción Múltiple (2-6 opciones, respuesta correcta obligatoria)
| - Verdadero/Falso (respuesta correcta obligatoria)
| - Pregunta Abierta (sin respuesta correcta ni feedback)
|
--}}
<x-layouts.app title="Test — {{ $patient->case_title }}">
    <x-slot:styles>
        <link href="{{ asset('css/patients.css') }}" rel="stylesheet">
        <link href="{{ asset('css/patient-test.css') }}" rel="stylesheet">
    </x-slot:styles>

    <x-navbar backRoute="patients.preview" :backRouteParams="$patient" backLabel="Volver al Preview" rightLabel="Gestión del Test" />

    <div class="container" style="margin-top: 30px;">

        {{-- Mensajes flash --}}
        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        {{-- Cabecera --}}
        <div class="test-header">
            <div>
                <h1>📝 Test de Evaluación</h1>
                <p class="test-subtitle">{{ $patient->case_title }}</p>
            </div>
            <div class="test-stats">
                <span class="test-stat">
                    <strong>{{ $questions->count() }}</strong> {{ $questions->count() === 1 ? 'pregunta' : 'preguntas' }}
                </span>
                <span class="test-stat">
                    <strong>{{ $questions->sum('points') }}</strong> puntos totales
                </span>
            </div>
        </div>

        {{-- PREGUNTAS EXISTENTES --}}
        @if($questions->isNotEmpty())
            <div class="questions-list">
                @foreach($questions as $index => $question)
                    <div class="question-card">
                        <div class="question-card-header">
                            <span class="question-number">{{ $index + 1 }}</span>
                            <span class="question-type-badge badge-{{ strtolower($question->question_type) }}">
                                {{ $question->type_label }}
                            </span>
                            <span class="question-points">{{ $question->points }} pts</span>
                            <form action="{{ route('patients.test.destroy', [$patient, $question]) }}" method="POST"
                                  onsubmit="return confirm('¿Eliminar esta pregunta?')" style="margin-left: auto;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn-delete-question" title="Eliminar pregunta">✕</button>
                            </form>
                        </div>
                        <div class="question-card-body">
                            <p class="question-text">{{ $question->question_text }}</p>

                            {{-- Opciones para múltiple elección --}}
                            @if($question->question_type === 'MULTIPLE_CHOICE' && $question->options)
                                <div class="question-options">
                                    @foreach($question->options as $option)
                                        <span class="option-pill {{ $option === $question->correct_answer ? 'option-correct' : '' }}">
                                            {{ $option }}
                                            @if($option === $question->correct_answer) ✓ @endif
                                        </span>
                                    @endforeach
                                </div>
                            @endif

                            {{-- Respuesta para V/F --}}
                            @if($question->question_type === 'TRUE_FALSE')
                                <p class="question-answer">
                                    Respuesta correcta: <strong>{{ $question->correct_answer === 'true' ? 'Verdadero' : 'Falso' }}</strong>
                                </p>
                            @endif

                            {{-- Feedback --}}
                            @if($question->feedback_correct || $question->feedback_incorrect)
                                <div class="question-feedback">
                                    @if($question->feedback_correct)
                                        <p class="feedback-correct">✅ {{ $question->feedback_correct }}</p>
                                    @endif
                                    @if($question->feedback_incorrect)
                                        <p class="feedback-incorrect">❌ {{ $question->feedback_incorrect }}</p>
                                    @endif
                                </div>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="empty-test">
                <p>No hay preguntas todavía. Añade al menos <strong>1 pregunta</strong> para poder publicar el paciente.</p>
            </div>
        @endif

        {{-- FORMULARIO PARA AÑADIR PREGUNTA --}}
        <div class="add-question-section">
            <h2 class="add-question-title">Añadir Pregunta</h2>

            {{-- Errores de validación --}}
            @if($errors->any())
                <div class="alert alert-danger">
                    <strong>Corrige los siguientes errores:</strong>
                    <ul>
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('patients.test.store', $patient) }}" method="POST" id="questionForm">
                @csrf

                {{-- Tipo de pregunta --}}
                <div class="form-group">
                    <label for="question_type">Tipo de Pregunta <span class="required">*</span></label>
                    <select id="question_type" name="question_type" required onchange="handleTypeChange()">
                        <option value="MULTIPLE_CHOICE" {{ old('question_type', 'MULTIPLE_CHOICE') === 'MULTIPLE_CHOICE' ? 'selected' : '' }}>Opción Múltiple</option>
                        <option value="TRUE_FALSE" {{ old('question_type') === 'TRUE_FALSE' ? 'selected' : '' }}>Verdadero / Falso</option>
                        <option value="OPEN_ENDED" {{ old('question_type') === 'OPEN_ENDED' ? 'selected' : '' }}>Pregunta Abierta</option>
                    </select>
                </div>

                {{-- Enunciado --}}
                <div class="form-group">
                    <label for="question_text">Enunciado <span class="required">*</span></label>
                    <textarea id="question_text" name="question_text" required
                              placeholder="Ej: ¿Cuál es el diagnóstico más probable para este paciente?">{{ old('question_text') }}</textarea>
                </div>

                {{-- Puntuación --}}
                <div class="form-group form-group-inline">
                    <label for="points">Puntuación <span class="required">*</span></label>
                    <input type="number" id="points" name="points" value="{{ old('points', 10) }}"
                           min="0.01" max="100" step="0.5" required style="max-width: 120px;">
                    <span class="hint">puntos</span>
                </div>

                {{-- OPCIONES: Solo para Opción Múltiple --}}
                <div id="multipleChoiceFields">
                    <div class="form-group">
                        <label>Opciones <span class="required">*</span>
                            <span class="hint">(mínimo 2, máximo 6)</span>
                        </label>
                        <div id="optionsContainer">
                            <div class="option-row">
                                <input type="text" name="options[]" value="{{ old('options.0') }}" placeholder="Opción A">
                                <button type="button" class="btn-remove-option" onclick="removeOption(this)" style="visibility: hidden;">✕</button>
                            </div>
                            <div class="option-row">
                                <input type="text" name="options[]" value="{{ old('options.1') }}" placeholder="Opción B">
                                <button type="button" class="btn-remove-option" onclick="removeOption(this)" style="visibility: hidden;">✕</button>
                            </div>
                        </div>
                        <button type="button" class="btn-add-option" id="btnAddOption" onclick="addOption()">+ Añadir opción</button>
                    </div>

                    <div class="form-group">
                        <label for="correct_answer_mc">Respuesta Correcta <span class="required">*</span>
                            <span class="hint">(escribe exactamente una de las opciones)</span>
                        </label>
                        <input type="text" id="correct_answer_mc" name="correct_answer" value="{{ old('correct_answer') }}"
                               placeholder="Escribe aquí la opción correcta tal cual">
                    </div>
                </div>

                {{-- VERDADERO/FALSO: Respuesta correcta --}}
                <div id="trueFalseFields" style="display: none;">
                    <div class="form-group">
                        <label>Respuesta Correcta <span class="required">*</span></label>
                        <div class="tf-options">
                            <label class="tf-option">
                                <input type="radio" name="correct_answer" value="true" {{ old('correct_answer') === 'true' ? 'checked' : '' }}>
                                <span>Verdadero</span>
                            </label>
                            <label class="tf-option">
                                <input type="radio" name="correct_answer" value="false" {{ old('correct_answer') === 'false' ? 'checked' : '' }}>
                                <span>Falso</span>
                            </label>
                        </div>
                    </div>
                </div>

                {{-- FEEDBACK: Solo para MC y V/F --}}
                <div id="feedbackFields">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="feedback_correct">Feedback si acierta <span class="hint">(opcional)</span></label>
                            <textarea id="feedback_correct" name="feedback_correct"
                                      placeholder="Ej: Correcto. Los hallazgos sugieren...">{{ old('feedback_correct') }}</textarea>
                        </div>
                        <div class="form-group">
                            <label for="feedback_incorrect">Feedback si falla <span class="hint">(opcional)</span></label>
                            <textarea id="feedback_incorrect" name="feedback_incorrect"
                                      placeholder="Ej: Incorrecto. Recuerda que el paciente presentaba...">{{ old('feedback_incorrect') }}</textarea>
                        </div>
                    </div>
                </div>

                {{-- Info para pregunta abierta --}}
                <div id="openEndedInfo" style="display: none;">
                    <div class="context-box">
                        <strong>ℹ️ Pregunta Abierta</strong>
                        <p>Las preguntas abiertas no tienen respuesta correcta ni feedback automático. Se evalúan manualmente o se dejan como reflexión para el estudiante.</p>
                    </div>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn-submit">
                        <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2">
                            <line x1="12" y1="5" x2="12" y2="19"/>
                            <line x1="5" y1="12" x2="19" y2="12"/>
                        </svg>
                        Añadir Pregunta
                    </button>
                </div>
            </form>
        </div>

        {{-- ACCIONES FINALES --}}
        <div class="test-actions">
            <a href="{{ route('patients.preview', $patient) }}" class="btn-large btn-secondary-large">
                ← Volver al Preview
            </a>

            @if(!$patient->is_published)
                @if($questions->count() >= 1)
                    <form action="{{ route('patients.publish', $patient) }}" method="POST" style="display: inline;">
                        @csrf
                        <button type="submit" class="btn-large btn-primary-large">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="20" height="20">
                                <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/>
                                <polyline points="22 4 12 14.01 9 11.01"/>
                            </svg>
                            Publicar Paciente
                        </button>
                    </form>
                @else
                    <span class="btn-large btn-disabled" title="Añade al menos 1 pregunta para publicar">
                        🔒 Publicar (necesitas al menos 1 pregunta)
                    </span>
                @endif
            @else
                <span class="btn-large" style="background: var(--color-success, #27ae60); color: white; cursor: default;">
                    ✓ Paciente Publicado
                </span>
            @endif
        </div>
    </div>

    <x-slot:scripts>
        <script src="{{ asset('js/patient-test.js') }}"></script>
    </x-slot:scripts>
</x-layouts.app>