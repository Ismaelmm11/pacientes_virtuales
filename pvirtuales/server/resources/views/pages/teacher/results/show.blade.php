{{--
|--------------------------------------------------------------------------
| Detalle y corrección de un intento — Profesor
|--------------------------------------------------------------------------
|
| Muestra estadísticas de la consulta y el test con las respuestas del
| alumno. Las preguntas abiertas se corrigen con un slider + textarea.
| Al final hay un campo de feedback general y un botón de guardar.
|
| Variables recibidas del controlador:
| $attempt → TestAttempt cargado con patient, user, answers.question
| $durationMinutes → int|null Duración de la consulta en minutos
| $studentMessages → int Nº mensajes del alumno en la consulta
| $correctCount → int Respuestas correctas automáticas
| $totalAnswered → int Total de respuestas del test
|
--}}

<x-layouts.app>

    <x-slot name="title">
        Corrección — {{ $attempt->user?->full_name ?? 'Alumno' }}
    </x-slot>

    <x-slot name="styles">
        <link href="{{ asset('css/dashboard.css') }}" rel="stylesheet">
        <link href="{{ asset('css/patients.css') }}" rel="stylesheet">
        <link href="{{ asset('css/results-show.css') }}" rel="stylesheet">
    </x-slot>

    {{-- =====================================================================
    TOPBAR
    ====================================================================== --}}
    <x-slot name="topbar">
        <div class="topbar">
            <div class="topbar-left">
                <div class="topbar-title">{{ $attempt->user?->full_name ?? 'Alumno' }}</div>
                <div class="topbar-subtitle">{{ $attempt->patient->case_title }}</div>
            </div>
            <div class="topbar-right">
                <a href="{{ route('teacher.results.patient', $attempt->patient) }}" class="btn btn-ghost btn-sm">
                    <i data-lucide="arrow-left"></i>
                    Volver
                </a>
            </div>
        </div>
    </x-slot>

    {{-- =====================================================================
    ESTADÍSTICAS (4 tarjetas)
    ====================================================================== --}}
    <div class="stats-grid">

        {{-- Duración de la consulta --}}
        <div class="stat-card">
            <div class="stat-card-icon secondary"><i data-lucide="clock"></i></div>
            <div class="stat-card-info">
                <div class="stat-card-value">
                    {{ $durationMinutes !== null ? $durationMinutes . ' min' : '—' }}
                </div>
                <div class="stat-card-label">Duración consulta</div>
            </div>
        </div>

        {{-- Mensajes enviados por el alumno --}}
        <div class="stat-card">
            <div class="stat-card-icon secondary"><i data-lucide="message-circle"></i></div>
            <div class="stat-card-info">
                <div class="stat-card-value">{{ $studentMessages }}</div>
                <div class="stat-card-label">Mensajes enviados</div>
            </div>
        </div>

        {{-- Respuestas correctas automáticas --}}
        <div class="stat-card">
            <div class="stat-card-icon primary"><i data-lucide="check-circle"></i></div>
            <div class="stat-card-info">
                <div class="stat-card-value">{{ $correctCount }} / {{ $totalAnswered }}</div>
                <div class="stat-card-label">Respuestas correctas</div>
            </div>
        </div>

        {{-- Nota final --}}
        <div class="stat-card">
            <div class="stat-card-icon primary"><i data-lucide="bar-chart-2"></i></div>
            <div class="stat-card-info">
                <div class="stat-card-value">
                    {{ $attempt->final_score !== null
    ? number_format($attempt->final_score, 2) . ' / 10'
    : '—' }}
                </div>
                <div class="stat-card-label">Nota final</div>
            </div>
        </div>

    </div>

    {{-- =====================================================================
    TRANSCRIPCIÓN DE LA CONSULTA (collapsible)
    ====================================================================== --}}

    {{-- Filtrar solo los mensajes visibles: user y assistant. Los mensajes
    de rol 'system' son el prompt interno y no deben mostrarse. --}}
    @php

        $transcript = collect($attempt->interview_transcript ?? [])
            ->filter(fn($m) => in_array($m['role'] ?? '', ['user', 'assistant']))
            ->values();
    @endphp

    <details class="transcript-details">

        <summary class="transcript-summary">
            <div class="transcript-summary-inner">
                <div class="transcript-summary-left">
                    <i data-lucide="message-square"></i>
                    Transcripción de la consulta
                </div>
                <div class="transcript-summary-right">
                    <span class="transcript-count">{{ $transcript->count() }} mensajes</span>
                    <i data-lucide="chevron-down" class="transcript-chevron"></i>
                </div>
            </div>
        </summary>

        <div class="transcript-body">

            @if($transcript->isEmpty())
                <div class="transcript-empty">Esta consulta no tiene mensajes registrados.</div>
            @else
                @foreach($transcript as $message)
                    @php
                        $role = $message['role'] ?? 'assistant';
                        $content = $message['content'] ?? '';

                        // -- Etiqueta del emisor: nombre del alumno o nombre del caso --
                        $senderLabel = $role === 'user'
                            ? ($attempt->user?->full_name ?? 'Alumno')
                            : ($attempt->patient->case_title ?? 'Paciente');
                    @endphp

                    <div class="chat-message {{ $role === 'user' ? 'chat-message-student' : 'chat-message-patient' }}">
                        <div class="chat-bubble">
                            <div class="chat-role-label">{{ $senderLabel }}</div>
                            <div class="chat-content">{{ $content }}</div>
                        </div>
                    </div>

                @endforeach
            @endif

        </div>{{-- /.transcript-body --}}

    </details>

    {{-- =====================================================================
    FORMULARIO: TEST CON RESPUESTAS + FEEDBACK GENERAL
    ====================================================================== --}}
    <form method="POST" action="{{ route('teacher.results.grade', $attempt) }}" style="margin-top: 28px;">
        @csrf

        {{-- Solo se muestran inputs para las preguntas abiertas.
        Las preguntas automáticas son de solo lectura. --}}

        @if($attempt->answers->isEmpty())
            <div class="empty-state">
                <div class="empty-state-icon"><i data-lucide="inbox"></i></div>
                <div class="empty-state-title">Sin respuestas</div>
                <div class="empty-state-text">Este alumno no respondió ninguna pregunta del test.</div>
            </div>
        @else
            <div class="questions-list">

                @foreach($attempt->answers as $answer)
                    @php
                        $q = $answer->question;
                        $type = $q->question_type;

                        // Puntos máximos de esta pregunta
                        $maxPoints = (float) ($q->points ?? 10);

                        // Step del slider: 10 pasos proporcionales a los puntos
                        $sliderStep = $maxPoints / 10;

                        // Determinar el estado visual de la respuesta
                        // Estado visual para preguntas abiertas con umbrales proporcionales al máximo
                        if ($type === 'OPEN_ENDED') {
                            if ($answer->score === null) {
                                // Aún no corregida
                                $statusClass = 'pending';
                                $statusLabel = 'Pendiente';
                            } else {
                                // Calcular el porcentaje sobre el máximo de la pregunta
                                // Umbrales: <50% rojo | 50-70% naranja | >=70% verde
                                $pct = $maxPoints > 0 ? ($answer->score / $maxPoints) * 100 : 0;

                                if ($pct < 50) {
                                    $statusClass = 'incorrect';
                                    $statusLabel = 'Insuficiente';
                                } elseif ($pct < 70) {
                                    $statusClass = 'warning';
                                    $statusLabel = 'Suficiente';
                                } else {
                                    $statusClass = 'correct';
                                    $statusLabel = 'Correcto';
                                }
                            }
                        } else {
                            $statusClass = $answer->is_correct ? 'correct' : 'incorrect';
                            $statusLabel = $answer->is_correct ? 'Correcto' : 'Incorrecto';
                        }

                        // Etiqueta del tipo
                        $typeLabel = match ($type) {
                            'MULTIPLE_CHOICE' => 'Múltiple opción',
                            'TRUE_FALSE' => 'Verdadero / Falso',
                            'OPEN_ENDED' => 'Abierta',
                            default => $type,
                        };
                    @endphp

                    <div class="question-card">

                        {{-- Cabecera: tipo y estado --}}
                        <div class="question-card-header">
                            <span class="q-type-label">{{ $typeLabel }}</span>
                            <span class="q-status {{ $statusClass }}">{{ $statusLabel }}</span>
                        </div>

                        <div class="question-card-body">

                            {{-- Enunciado de la pregunta --}}
                            <div class="question-card-text">{{ $q->question_text }}</div>

                            {{-- ================================================
                            OPCIÓN MÚLTIPLE: mostrar todas las opciones
                            ================================================ --}}
                            @if($type === 'MULTIPLE_CHOICE' && !empty($q->options))
                                <div class="question-options">
                                    @foreach($q->options as $option)
                                        @php
                                            $isCorrectOption = (string) $option === (string) $q->correct_answer;
                                            $isStudentChoice = (string) $option === (string) $answer->given_answer;
                                            $isWrongChoice = $isStudentChoice && !$isCorrectOption;
                                        @endphp
                                        <div
                                            class="option-item
                                                                                                                            {{ $isCorrectOption ? 'is-correct' : '' }}
                                                                                                                            {{ $isWrongChoice ? 'is-student-wrong' : '' }}">
                                            <span class="option-dot"></span>
                                            <span class="option-text">{{ $option }}</span>
                                            @if($isWrongChoice)
                                                <span class="option-student-tag">Respuesta del alumno</span>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>

                                {{-- Feedback de la pregunta (correcto o incorrecto) --}}
                                @php
                                    $feedbackText = $answer->is_correct
                                        ? $q->feedback_correct
                                        : $q->feedback_incorrect;
                                @endphp
                                @if($feedbackText)
                                    <div class="question-feedback-box">{{ $feedbackText }}</div>
                                @endif

                                {{-- ================================================
                                VERDADERO / FALSO: dos opciones
                                ================================================ --}}
                            @elseif($type === 'TRUE_FALSE')
                                <div class="question-options">
                                    @foreach(['Verdadero', 'Falso'] as $tfOption)
                                        @php
                                            // Normalizar para comparar independientemente del formato guardado
                                            $tfNorm = strtolower($tfOption);
                                            $correctNorm = strtolower((string) $q->correct_answer);
                                            $studentNorm = strtolower((string) $answer->given_answer);

                                            $isCorrectOption = in_array($correctNorm, [$tfNorm, $tfNorm === 'verdadero' ? 'true' : 'false']);
                                            $isStudentChoice = in_array($studentNorm, [$tfNorm, $tfNorm === 'verdadero' ? 'true' : 'false']);
                                            $isWrongChoice = $isStudentChoice && !$isCorrectOption;
                                        @endphp
                                        <div
                                            class="option-item
                                                                                                                        {{ $isCorrectOption ? 'is-correct' : '' }}
                                                                                                                        {{ $isWrongChoice ? 'is-student-wrong' : '' }}">
                                            <span class="option-dot"></span>
                                            <span class="option-text">{{ $tfOption }}</span>
                                            @if($isWrongChoice)
                                                <span class="option-student-tag">Respuesta del alumno</span>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>

                                {{-- Feedback de la pregunta --}}
                                @php
                                    $feedbackText = $answer->is_correct
                                        ? $q->feedback_correct
                                        : $q->feedback_incorrect;
                                @endphp
                                @if($feedbackText)
                                    <div class="question-feedback-box">{{ $feedbackText }}</div>
                                @endif

                                {{-- ================================================
                                PREGUNTA ABIERTA: respuesta + corrección
                                ================================================ --}}
                            @elseif($type === 'OPEN_ENDED')

                                    {{-- Respuesta escrita por el alumno --}}
                                    <div class="student-open-answer">
                                        {{ $answer->given_answer ?? '—' }}
                                    </div>

                                    {{-- Zona de corrección del profesor --}}
                                    <div class="open-grading">

                                        {{-- Slider de puntuación --}}
                                        <div>
                                            <div class="open-grading-label">
                                                Puntuación (máx. {{ number_format($maxPoints, 0) }} pts)
                                            </div>
                                            <div class="score-slider-wrapper" style="margin-top: 8px;">
                                                <input type="range" class="score-slider" name="scores[{{ $answer->id }}]" min="0"
                                                    max="{{ $maxPoints }}" step="{{ $sliderStep }}" value="{{ $answer->score ?? 0 }}"
                                                    data-max="{{ $maxPoints }}">
                                                {{-- El JS actualiza este span al mover el slider --}}
                                                <span class="score-value-display">
                                                    {{ $answer->score !== null
                                ? number_format((float) $answer->score, 1) . ' / ' . number_format($maxPoints, 0)
                                : '0 / ' . number_format($maxPoints, 0) }}
                                                </span>
                                            </div>
                                        </div>

                                        {{-- Feedback del profesor para esta respuesta --}}
                                        <div>
                                            <div class="open-grading-label">
                                                Justificación / comentario al alumno
                                            </div>
                                            <textarea class="open-feedback-textarea" name="feedbacks[{{ $answer->id }}]"
                                                placeholder="Justifica la nota, indica qué falta o qué mejorar..."
                                                style="margin-top: 8px;">{{ $answer->feedback ?? '' }}</textarea>
                                        </div>

                                    </div>{{-- /.open-grading --}}

                            @endif

                        </div>{{-- /.question-card-body --}}
                    </div>{{-- /.question-card --}}

                @endforeach

            </div>{{-- /.questions-list --}}
        @endif

        {{-- ===================================================================
        FEEDBACK GENERAL DEL EXAMEN
        ==================================================================== --}}
        <div class="general-feedback-section">
            <h3>
                <i data-lucide="message-square"
                    style="width:16px;height:16px;vertical-align:-2px;margin-right:6px;"></i>
                Feedback general del examen
            </h3>
            <textarea class="general-feedback-textarea" name="general_feedback"
                placeholder="Escribe aquí un comentario general sobre el desempeño del alumno en este examen...">{{ $attempt->general_feedback ?? '' }}</textarea>
        </div>

        {{-- ===================================================================
        BOTÓN GUARDAR
        ==================================================================== --}}
        <div class="save-row">
            <button type="submit" class="btn btn-primary">
                <i data-lucide="save"></i>
                Guardar corrección
            </button>
        </div>

    </form>

    <x-slot name="scripts">
        <script>
            (function () {
                // Actualizar el valor mostrado junto a cada slider al moverlo
                document.querySelectorAll('.score-slider').forEach(function (slider) {
                    var display = slider.closest('.score-slider-wrapper')
                        .querySelector('.score-value-display');
                    var maxPts = parseFloat(slider.dataset.max);

                    slider.addEventListener('input', function () {
                        var val = parseFloat(this.value);
                        // Mostrar sin decimales si es número entero, con 1 decimal si no
                        var formatted = (val % 1 === 0)
                            ? val.toFixed(0)
                            : val.toFixed(1);
                        display.textContent = formatted + ' / ' + maxPts.toFixed(0);
                    });
                });
            })();
        </script>
    </x-slot>

</x-layouts.app>