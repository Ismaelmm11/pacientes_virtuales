{{--
|--------------------------------------------------------------------------
| Gestión del Test de Evaluación
|--------------------------------------------------------------------------
--}}
<x-layouts.app>

    <x-slot name="title">Test — {{ $patient->case_title }}</x-slot>

    <x-slot name="styles">
        <link href="{{ asset('css/create-patient.css') }}" rel="stylesheet">
        <link href="{{ asset('css/dashboard.css') }}" rel="stylesheet">
    </x-slot>

    <x-slot name="topbar">
        <div class="topbar">
            <div class="topbar-left">
                <div class="topbar-title">Test de Evaluación</div>
                <div class="topbar-subtitle">
                    <span class="mode-badge">
                        <i data-lucide="clipboard-list"></i>
                        {{ $patient->case_title }}
                    </span>
                </div>
            </div>
            <div class="topbar-right">
                <a href="{{ route('teacher.patients.preview', $patient) }}" class="btn btn-ghost btn-sm">
                    <i data-lucide="arrow-left"></i>
                    Volver al Paciente
                </a>
                @if(!$patient->is_published)
                    <form action="{{ route('teacher.patients.publish', $patient) }}" method="POST" class="cp-form-inline">
                        @csrf
                        <button type="submit" class="btn btn-primary btn-sm" {{ $questions->isEmpty() ? 'disabled' : '' }}>
                            <i data-lucide="send"></i>
                            Publicar Paciente
                        </button>
                    </form>
                @else
                    <form action="{{ route('teacher.patients.publish', $patient) }}" method="POST" class="cp-form-inline">
                        @csrf
                        <button type="submit" class="btn btn-ghost btn-sm">
                            <i data-lucide="eye-off"></i>
                            Despublicar
                        </button>
                    </form>
                @endif
            </div>
        </div>
    </x-slot>

    @if(session('success'))
        <div class="cp-alert-success">
            <div class="cp-alert-success-icon"><i data-lucide="circle-check"></i></div>
            <div class="cp-alert-success-text">{{ session('success') }}</div>
        </div>
    @endif

    @if(session('error'))
        <div class="cp-alert-error">
            <div class="cp-alert-icon"><i data-lucide="circle-alert"></i></div>
            <div class="cp-alert-body">{{ session('error') }}</div>
        </div>
    @endif

    <div class="create-patient-layout">
        <div class="create-patient-main">

            {{-- ===== SECCIÓN 1: CONFIGURACIÓN DEL TEST ===== --}}
            <div class="cp-section">
                <div class="cp-section-header">
                    <div class="cp-section-icon"><i data-lucide="settings"></i></div>
                    <h2 class="cp-section-title">Configuración del Test</h2>
                </div>
                <p class="cp-section-desc">Define cómo se comporta el test cuando el alumno lo realiza.</p>

                <form action="{{ route('teacher.patients.test.config', $patient) }}" method="POST" id="configForm">
                    @csrf
                    @method('PUT')

                    <div>

                        {{-- Intentos máximos --}}
                        <div class="cp-form-group">
                            <div class="cp-label-row">
                                <label for="max_attempts">Intentos máximos</label>
                                <span class="help-tooltip">
                                    <span class="help-tooltip-icon">?</span>
                                    <span class="help-tooltip-bubble">
                                        <strong>¿Para qué sirve?</strong>
                                        Número de veces que un alumno puede simular este paciente.
                                        Selecciona "Ilimitados" si no quieres restringirlo.
                                    </span>
                                </span>
                            </div>

                            {{-- Checkbox para activar intentos ilimitados --}}
                            <label class="cp-toggle-label">
                                <input type="checkbox" id="unlimited_attempts" {{ old('max_attempts', $patient->max_attempts) == -1 ? 'checked' : '' }}
                                    onchange="toggleUnlimitedAttempts(this)">
                                Ilimitados
                            </label>

                            {{-- Input numérico (oculto si se activó "Ilimitados") --}}
                            <input type="number" id="max_attempts" name="max_attempts" min="1" max="10"
                                value="{{ old('max_attempts', $patient->max_attempts) == -1 ? 1 : old('max_attempts', $patient->max_attempts) }}"
                                {{ old('max_attempts', $patient->max_attempts) == -1 ? 'disabled hidden' : '' }}>

                            {{-- Input oculto que envía -1 cuando está marcado "Ilimitados" --}}
                            <input type="hidden" id="max_attempts_unlimited" name="max_attempts" value="-1" {{ old('max_attempts', $patient->max_attempts) == -1 ? '' : 'disabled' }}>
                        </div>

                        {{-- Aleatorización --}}
                        <div class="cp-form-group">
                            <div class="cp-label-row">
                                <label>¿Preguntas aleatorias?</label>
                                <span class="help-tooltip">
                                    <span class="help-tooltip-icon">?</span>
                                    <span class="help-tooltip-bubble">
                                        Si está activo, cada intento mostrará un subconjunto aleatorio de preguntas.
                                        Deberás definir cuántas aparecen por test.
                                    </span>
                                </span>
                            </div>
                            <div class="cp-attendee-selector">
                                <div class="cp-attendee-option">
                                    <input type="radio" name="randomize_questions" id="random_no" value="0" {{ !$patient->randomize_questions ? 'checked' : '' }}
                                        onchange="toggleRandomConfig(false)">
                                    <label for="random_no">
                                        <span class="attendee-icon">📋</span>
                                        <span class="attendee-label">No</span>
                                        <span class="attendee-desc">Aparecen todas las preguntas</span>
                                    </label>
                                </div>
                                <div class="cp-attendee-option">
                                    <input type="radio" name="randomize_questions" id="random_yes" value="1" {{ $patient->randomize_questions ? 'checked' : '' }}
                                        onchange="toggleRandomConfig(true)">
                                    <label for="random_yes">
                                        <span class="attendee-icon">🎲</span>
                                        <span class="attendee-label">Sí</span>
                                        <span class="attendee-desc">Subconjunto aleatorio por intento</span>
                                    </label>
                                </div>
                            </div>
                        </div>

                    </div>

                    {{-- Preguntas por test (solo si aleatorio) --}}
                    <div class="cp-random-config {{ $patient->randomize_questions ? 'visible' : '' }}"
                        id="randomConfig">
                        <div class="cp-form-group">
                            <div class="cp-label-row">
                                <label for="questions_per_test">Preguntas por test<span
                                        class="required">*</span></label>
                                <span class="help-tooltip">
                                    <span class="help-tooltip-icon">?</span>
                                    <span class="help-tooltip-bubble">
                                        Cuántas preguntas aparecen en cada intento. Las obligatorias siempre aparecen y
                                        cuentan dentro de este límite.
                                    </span>
                                </span>
                            </div>
                            <input type="number" id="questions_per_test" name="questions_per_test" min="1"
                                value="{{ old('questions_per_test', $patient->questions_per_test) }}"
                                placeholder="Ej: 5">
                            @php
                                $required = $questions->where('is_required', true)->count();
                                $total = $questions->count();
                            @endphp
                            @if($total > 0)
                                <p class="cp-field-hint">
                                    Tienes {{ $total }} preguntas ({{ $required }} obligatorias).
                                    Para que haya aleatoriedad real necesitas más preguntas no obligatorias que el límite
                                    menos las obligatorias.
                                </p>
                            @endif
                        </div>
                    </div>

                    {{-- Orden aleatorio --}}
                    <div class="cp-form-group">
                        <div class="cp-label-row">
                            <label>¿Orden aleatorio?</label>
                            <span class="help-tooltip">
                                <span class="help-tooltip-icon">?</span>
                                <span class="help-tooltip-bubble">
                                    Si está activo, las preguntas aparecen en un orden diferente en cada intento.
                                    Es independiente de la aleatorización del banco de preguntas.
                                </span>
                            </span>
                        </div>
                        <div class="cp-attendee-selector">
                            <div class="cp-attendee-option">
                                <input type="radio" name="randomize_order" id="order_no" value="0" {{ !$patient->randomize_order ? 'checked' : '' }}>
                                <label for="order_no">
                                    <span class="attendee-icon">📋</span>
                                    <span class="attendee-label">No</span>
                                    <span class="attendee-desc">Siempre en el mismo orden</span>
                                </label>
                            </div>
                            <div class="cp-attendee-option">
                                <input type="radio" name="randomize_order" id="order_yes" value="1" {{ $patient->randomize_order ? 'checked' : '' }}>
                                <label for="order_yes">
                                    <span class="attendee-icon">🔀</span>
                                    <span class="attendee-label">Sí</span>
                                    <span class="attendee-desc">Orden distinto en cada intento</span>
                                </label>
                            </div>
                        </div>
                    </div>


                    <div class="cp-form-actions">
                        <button type="submit" class="btn btn-primary btn-sm">
                            <i data-lucide="save"></i>
                            Guardar Configuración
                        </button>
                    </div>

                </form>
            </div>

            {{-- ===== SECCIÓN 2: PREGUNTAS EXISTENTES ===== --}}
            <div class="cp-section">
                <div class="cp-section-header">
                    <div class="cp-section-icon"><i data-lucide="list"></i></div>
                    <h2 class="cp-section-title">Preguntas del Test</h2>
                </div>
                <p class="cp-section-desc">
                    {{ $questions->count() }} {{ $questions->count() === 1 ? 'pregunta creada' : 'preguntas creadas' }}.
                    Necesitas al menos una para poder publicar el paciente.
                </p>

                @if($questions->isEmpty())
                    <div class="cp-empty-state">
                        <div class="cp-empty-icon"><i data-lucide="clipboard-x"></i></div>
                        <p class="cp-empty-title">Aún no hay preguntas</p>
                        <p class="cp-empty-desc">Usa el formulario de abajo para añadir la primera.</p>
                    </div>
                @else
                    <div class="cp-question-list">
                        @foreach($questions as $index => $question)
                                        <div class="cp-question-item">
                                            <div class="cp-question-item-left">
                                                <span class="cp-question-number">{{ $index + 1 }}</span>
                                                <div class="cp-question-body">
                                                    <div class="cp-question-text">{{ $question->question_text }}</div>
                                                    <div class="cp-question-meta">
                                                        <span
                                                            class="badge {{ $question->question_type === 'MULTIPLE_CHOICE' ? 'badge-primary' : ($question->question_type === 'TRUE_FALSE' ? 'badge-secondary' : 'badge-warning') }}">
                                                            {{ $question->type_label }}
                                                        </span>
                                                        @if($question->is_required)
                                                            <span class="badge badge-danger">Obligatoria</span>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="cp-question-item-right">

                                                <button type="button" class="btn-action btn-action-edit" onclick="loadQuestionForEdit({{ Js::from([
                                'id' => $question->id,
                                'question_text' => $question->question_text,
                                'question_type' => $question->question_type,
                                'options' => $question->options,
                                'correct_answer' => $question->correct_answer,
                                'feedback_correct' => $question->feedback_correct,
                                'feedback_incorrect' => $question->feedback_incorrect,
                                'is_required' => $question->is_required,
                            ]) }})" title="Editar">
                                                    <i data-lucide="pencil"></i>
                                                </button>

                                                <form action="{{ route('teacher.patients.test.destroy', [$patient, $question]) }}"
                                                    method="POST" onsubmit="return confirm('¿Eliminar esta pregunta?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn-action btn-action-danger" title="Eliminar">
                                                        <i data-lucide="trash-2"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                        @endforeach
                    </div>
                @endif
            </div>

            {{-- ===== SECCIÓN 3: AÑADIR PREGUNTA ===== --}}
            <div class="cp-section">
                <div class="cp-section-header">
                    <div class="cp-section-icon"><i data-lucide="plus-circle"></i></div>
                    <h2 class="cp-section-title">Añadir Pregunta</h2>
                    <h2 class="cp-section-title" id="questionFormTitle">Añadir Pregunta</h2>
                </div>
                <p class="cp-section-desc">Elige el tipo y rellena los campos. Los campos cambian según el tipo
                    seleccionado.</p>

                <form action="{{ route('teacher.patients.test.store', $patient) }}" method="POST" id="questionForm"
                    data-store-url="{{ route('teacher.patients.test.store', $patient) }}">

                    @csrf

                    @if($errors->any())
                        <div class="cp-alert-error">
                            <div class="cp-alert-icon"><i data-lucide="circle-alert"></i></div>
                            <div class="cp-alert-body">
                                <strong>Corrige los errores:</strong>
                                <ul>
                                    @foreach($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    @endif

                    {{-- Tipo de pregunta --}}
                    <div class="cp-form-group">
                        <div class="cp-label-row">
                            <label>Tipo de Pregunta <span class="required">*</span></label>
                        </div>
                        <div class="cp-question-type-selector">
                            @foreach(\App\Models\Question::typeLabels() as $value => $label)
                                <div class="cp-attendee-option">
                                    <input type="radio" name="question_type" id="type_{{ $value }}" value="{{ $value }}" {{ old('question_type') === $value ? 'checked' : '' }}
                                        onchange="switchQuestionType('{{ $value }}')">
                                    <label for="type_{{ $value }}">
                                        <span class="attendee-icon">
                                            {{ $value === 'MULTIPLE_CHOICE' ? '🔘' : ($value === 'TRUE_FALSE' ? '✅' : '✍️') }}
                                        </span>
                                        <span class="attendee-label">{{ $label }}</span>
                                    </label>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    {{-- Enunciado --}}
                    <div class="cp-form-group">
                        <div class="cp-label-row">
                            <label for="question_text">Enunciado <span class="required">*</span></label>
                        </div>
                        <textarea id="question_text" name="question_text"
                            placeholder="Ej: ¿Cuál es el diagnóstico más probable para este paciente?">{{ old('question_text') }}</textarea>
                    </div>

                    {{-- Campos dinámicos: Opción múltiple --}}
                    <div class="cp-question-fields" id="fields_MULTIPLE_CHOICE">
                        <div class="cp-form-group">
                            <div class="cp-label-row">
                                <label>Opciones <span class="required">*</span></label>
                            </div>
                            <div class="cp-options-list" id="optionsList">
                                @if(old('question_type') === 'MULTIPLE_CHOICE' && old('options'))
                                    @foreach(old('options') as $i => $opt)
                                        <div class="cp-option-item">
                                            <span class="cp-option-letter">{{ chr(65 + $i) }}</span>
                                            <input type="text" name="options[]" value="{{ $opt }}"
                                                placeholder="Opción {{ chr(65 + $i) }}">
                                            <button type="button" class="cp-btn-remove" onclick="removeOption(this)">
                                                <i data-lucide="x"></i>
                                            </button>
                                        </div>
                                    @endforeach
                                @else
                                    <div class="cp-option-item">
                                        <span class="cp-option-letter">A</span>
                                        <input type="text" name="options[]" placeholder="Opción A">
                                        <button type="button" class="cp-btn-remove" onclick="removeOption(this)"
                                            style="visibility:hidden">
                                            <i data-lucide="x"></i>
                                        </button>
                                    </div>
                                    <div class="cp-option-item">
                                        <span class="cp-option-letter">B</span>
                                        <input type="text" name="options[]" placeholder="Opción B">
                                        <button type="button" class="cp-btn-remove" onclick="removeOption(this)"
                                            style="visibility:hidden">
                                            <i data-lucide="x"></i>
                                        </button>
                                    </div>
                                @endif
                            </div>
                            <button type="button" class="cp-btn-add" onclick="addOption()" id="btnAddOption">
                                <i data-lucide="plus"></i>
                                Añadir Opción
                            </button>
                        </div>
                        <div class="cp-form-group">
                            <div class="cp-label-row">
                                <label for="correct_answer_mc">Respuesta Correcta <span
                                        class="required">*</span></label>
                            </div>
                            <select id="correct_answer_mc" name="correct_answer">
                                <option value="">Selecciona la respuesta correcta...</option>
                            </select>
                        </div>
                        <div class="cp-form-row">
                            <div class="cp-form-group">
                                <div class="cp-label-row">
                                    <label for="feedback_correct_mc">Feedback si acierta <span
                                            class="hint">(opcional)</span></label>
                                </div>
                                <textarea id="feedback_correct_mc" name="feedback_correct"
                                    placeholder="Ej: Correcto. El dolor precordial con irradiación es el signo clave.">{{ old('feedback_correct') }}</textarea>
                            </div>
                            <div class="cp-form-group">
                                <div class="cp-label-row">
                                    <label for="feedback_incorrect_mc">Feedback si falla <span
                                            class="hint">(opcional)</span></label>
                                </div>
                                <textarea id="feedback_incorrect_mc" name="feedback_incorrect"
                                    placeholder="Ej: Incorrecto. Revisa los síntomas del paciente.">{{ old('feedback_incorrect') }}</textarea>
                            </div>
                        </div>
                    </div>

                    {{-- Campos dinámicos: Verdadero / Falso --}}
                    <div class="cp-question-fields" id="fields_TRUE_FALSE">
                        <div class="cp-form-group">
                            <div class="cp-label-row">
                                <label>Respuesta Correcta <span class="required">*</span></label>
                            </div>
                            <div class="cp-attendee-selector">
                                <div class="cp-attendee-option">
                                    <input type="radio" name="correct_answer" id="tf_true" value="true" {{ old('correct_answer') === 'true' ? 'checked' : '' }}>
                                    <label for="tf_true">
                                        <span class="attendee-icon">✅</span>
                                        <span class="attendee-label">Verdadero</span>
                                    </label>
                                </div>
                                <div class="cp-attendee-option">
                                    <input type="radio" name="correct_answer" id="tf_false" value="false" {{ old('correct_answer') === 'false' ? 'checked' : '' }}>
                                    <label for="tf_false">
                                        <span class="attendee-icon">❌</span>
                                        <span class="attendee-label">Falso</span>
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class="cp-form-row">
                            <div class="cp-form-group">
                                <div class="cp-label-row">
                                    <label>Feedback si acierta <span class="hint">(opcional)</span></label>
                                </div>
                                <textarea name="feedback_correct"
                                    placeholder="Ej: Correcto.">{{ old('feedback_correct') }}</textarea>
                            </div>
                            <div class="cp-form-group">
                                <div class="cp-label-row">
                                    <label>Feedback si falla <span class="hint">(opcional)</span></label>
                                </div>
                                <textarea name="feedback_incorrect"
                                    placeholder="Ej: Incorrecto.">{{ old('feedback_incorrect') }}</textarea>
                            </div>
                        </div>
                    </div>

                    {{-- Obligatoria --}}
                    <div class="cp-form-group cp-random-config {{ $patient->randomize_questions ? 'visible' : '' }}"
                        id="requiredField">
                        <div class="cp-label-row">
                            <label>¿Pregunta obligatoria?</label>
                            <span class="help-tooltip">
                                <span class="help-tooltip-icon">?</span>
                                <span class="help-tooltip-bubble">
                                    Si el test es aleatorio, las preguntas obligatorias aparecen siempre. Solo es
                                    relevante si activaste la aleatorización.
                                </span>
                            </span>
                        </div>
                        <div class="cp-attendee-selector">
                            <div class="cp-attendee-option">
                                <input type="radio" name="is_required" id="req_no" value="0" {{ old('is_required', '0') === '0' ? 'checked' : '' }}>
                                <label for="req_no">
                                    <span class="attendee-label">No obligatoria</span>
                                    <span class="attendee-desc">Puede aparecer o no en intentos aleatorios</span>
                                </label>
                            </div>
                            <div class="cp-attendee-option">
                                <input type="radio" name="is_required" id="req_yes" value="1" {{ old('is_required') === '1' ? 'checked' : '' }}>
                                <label for="req_yes">
                                    <span class="attendee-label">Obligatoria</span>
                                    <span class="attendee-desc">Aparece siempre, cuenta dentro del límite</span>
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="cp-form-actions">
                        <button type="button" class="btn btn-ghost" id="btnCancelEdit" onclick="cancelEdit()"
                            style="display:none"> {{-- excepción: estado inicial --}}
                            <i data-lucide="x"></i>
                            Cancelar Edición
                        </button>
                        <button type="reset" class="btn btn-ghost" onclick="resetForm()">Limpiar</button>
                        <button type="submit" class="btn btn-primary" id="btnSubmitQuestion">
                            <i data-lucide="plus"></i>
                            Añadir Pregunta
                        </button>
                    </div>

                </form>
            </div>

        </div>

        {{-- ===== SIDEBAR ===== --}}
        <aside class="create-patient-sidebar">
            <div class="cp-sidebar-card">
                <div class="cp-sidebar-title">Estado del Test</div>
                <div class="cp-sidebar-details">
                    <div>
                        <div class="cp-sidebar-detail-label">Total preguntas</div>
                        <div>{{ $questions->count() }}</div>
                    </div>
                    <div>
                        <div class="cp-sidebar-detail-label">Obligatorias</div>
                        <div>{{ $questions->where('is_required', true)->count() }}</div>
                    </div>
                    <div>
                        <div class="cp-sidebar-detail-label">Intentos máximos</div>
                        <div>
                            {{ $patient->max_attempts === -1 ? 'Ilimitadas' : $patient->max_attempts }}
                        </div>
                    </div>
                    <div>
                        <div class="cp-sidebar-detail-label">Aleatorización</div>
                        <div>{{ $patient->randomize_questions ? 'Sí' : 'No' }}</div>
                    </div>
                    @if($patient->randomize_questions && $patient->questions_per_test)
                        <div>
                            <div class="cp-sidebar-detail-label">Preguntas por test</div>
                            <div>{{ $patient->questions_per_test }}</div>
                        </div>
                    @endif
                    <div>
                        <div class="cp-sidebar-detail-label">Orden aleatorio</div>
                        <div>{{ $patient->randomize_order ? 'Sí' : 'No' }}</div>
                    </div>
                </div>
            </div>

            <div class="cp-sidebar-card cp-sidebar-info">
                <div class="cp-sidebar-info-icon"><i data-lucide="info"></i></div>
                <div class="cp-sidebar-info-title">Tipos de Pregunta</div>
                <div class="cp-sidebar-info-text">
                    <strong>Opción múltiple</strong> — El alumno elige entre varias respuestas.<br><br>
                    <strong>Verdadero/Falso</strong> — El alumno decide si una afirmación es cierta.<br><br>
                    <strong>Pregunta abierta</strong> — El alumno responde con texto libre.
                </div>
            </div>
        </aside>

    </div>

    <x-slot name="scripts">
        <script src="{{ asset('js/test-manage.js') }}"></script>
    </x-slot>

</x-layouts.app>