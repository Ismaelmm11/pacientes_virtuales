<x-layouts.app>

    <x-slot name="title">Mi resultado — {{ $attempt->patient->case_title }}</x-slot>

    <x-slot name="styles">
        <link href="{{ asset('css/dashboard.css') }}" rel="stylesheet">
        <link href="{{ asset('css/patients.css') }}" rel="stylesheet">
        <link href="{{ asset('css/results-show.css') }}" rel="stylesheet">
    </x-slot>

    <x-slot name="topbar">
        <div class="topbar">
            <div class="topbar-left">
                <div class="topbar-title">{{ $attempt->patient->case_title }}</div>
                <div class="topbar-subtitle">Detalle de mi resultado</div>
            </div>
            <div class="topbar-right">
                <a href="{{ route('student.results.index') }}" class="btn btn-ghost btn-sm">
                    <i data-lucide="arrow-left"></i>
                    Volver
                </a>
            </div>
        </div>
    </x-slot>

    {{-- ===== STATS ===== --}}
    <div class="stats-grid">

        <div class="stat-card">
            <div class="stat-card-icon primary"><i data-lucide="bar-chart-2"></i></div>
            <div class="stat-card-info">
                <div class="stat-card-value">
                    {{ $attempt->final_score !== null ? number_format($attempt->final_score, 2) . ' / 10' : '—' }}
                </div>
                <div class="stat-card-label">Nota final</div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-card-icon secondary"><i data-lucide="clock"></i></div>
            <div class="stat-card-info">
                <div class="stat-card-value">
                    {{ $durationMinutes !== null ? $durationMinutes . ' min' : '—' }}
                </div>
                <div class="stat-card-label">Duración consulta</div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-card-icon secondary"><i data-lucide="message-circle"></i></div>
            <div class="stat-card-info">
                <div class="stat-card-value">{{ $studentMessages }}</div>
                <div class="stat-card-label">Mensajes enviados</div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-card-icon primary"><i data-lucide="check-circle"></i></div>
            <div class="stat-card-info">
                <div class="stat-card-value">{{ $correctCount }} / {{ $totalAnswered }}</div>
                <div class="stat-card-label">Respuestas correctas</div>
            </div>
        </div>

    </div>

    {{-- ===== TRANSCRIPCIÓN ===== --}}
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
                        $role    = $message['role'] ?? 'assistant';
                        $content = $message['content'] ?? '';
                        $label   = $role === 'user' ? 'Tú' : ($attempt->patient->case_title ?? 'Paciente');
                    @endphp
                    <div class="chat-message {{ $role === 'user' ? 'chat-message-student' : 'chat-message-patient' }}">
                        <div class="chat-bubble">
                            <div class="chat-role-label">{{ $label }}</div>
                            <div class="chat-content">{{ $content }}</div>
                        </div>
                    </div>
                @endforeach
            @endif
        </div>
    </details>

    {{-- ===== PREGUNTAS ===== --}}
    @if($attempt->answers->isEmpty())
        <div class="empty-state mt-lg">
            <div class="empty-state-icon"><i data-lucide="inbox"></i></div>
            <div class="empty-state-title">Sin respuestas registradas</div>
        </div>
    @else
        <div class="questions-list mt-lg">

            @foreach($attempt->answers as $answer)
                @php
                    $q         = $answer->question;
                    $type      = $q->question_type;
                    $maxPoints = (float) ($q->points ?? 10);

                    if ($type === 'OPEN_ENDED') {
                        if ($answer->score === null) {
                            $statusClass = 'pending';   $statusLabel = 'Pendiente';
                        } else {
                            $pct = $maxPoints > 0 ? ($answer->score / $maxPoints) * 100 : 0;
                            if ($pct < 50)      { $statusClass = 'incorrect'; $statusLabel = 'Insuficiente'; }
                            elseif ($pct < 70)  { $statusClass = 'warning';   $statusLabel = 'Suficiente'; }
                            else                { $statusClass = 'correct';   $statusLabel = 'Correcto'; }
                        }
                    } else {
                        $statusClass = $answer->is_correct ? 'correct'   : 'incorrect';
                        $statusLabel = $answer->is_correct ? 'Correcto'  : 'Incorrecto';
                    }

                    $typeLabel = match($type) {
                        'MULTIPLE_CHOICE' => 'Múltiple opción',
                        'TRUE_FALSE'      => 'Verdadero / Falso',
                        'OPEN_ENDED'      => 'Abierta',
                        default           => $type,
                    };
                @endphp

                <div class="question-card">
                    <div class="question-card-header">
                        <span class="q-type-label">{{ $typeLabel }}</span>
                        <span class="q-status {{ $statusClass }}">{{ $statusLabel }}</span>
                    </div>

                    <div class="question-card-body">
                        <div class="question-card-text">{{ $q->question_text }}</div>

                        {{-- OPCIÓN MÚLTIPLE --}}
                        @if($type === 'MULTIPLE_CHOICE' && !empty($q->options))
                            <div class="question-options">
                                @foreach($q->options as $option)
                                    @php
                                        $isCorrectOption = (string) $option === (string) $q->correct_answer;
                                        $isStudentChoice = (string) $option === (string) $answer->given_answer;
                                        $isWrongChoice   = $isStudentChoice && !$isCorrectOption;
                                    @endphp
                                    <div class="option-item {{ $isCorrectOption ? 'is-correct' : '' }} {{ $isWrongChoice ? 'is-student-wrong' : '' }}">
                                        <span class="option-dot"></span>
                                        <span class="option-text">{{ $option }}</span>
                                        @if($isStudentChoice && $isCorrectOption)
                                            <span class="option-student-tag">Tu respuesta ✓</span>
                                        @elseif($isWrongChoice)
                                            <span class="option-student-tag">Tu respuesta</span>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                            @php $feedbackText = $answer->is_correct ? $q->feedback_correct : $q->feedback_incorrect; @endphp
                            @if($feedbackText)
                                <div class="question-feedback-box">{{ $feedbackText }}</div>
                            @endif

                        {{-- VERDADERO / FALSO --}}
                        @elseif($type === 'TRUE_FALSE')
                            <div class="question-options">
                                @foreach(['Verdadero', 'Falso'] as $tfOption)
                                    @php
                                        $tfNorm      = strtolower($tfOption);
                                        $correctNorm = strtolower((string) $q->correct_answer);
                                        $studentNorm = strtolower((string) $answer->given_answer);
                                        $isCorrectOption = in_array($correctNorm, [$tfNorm, $tfNorm === 'verdadero' ? 'true' : 'false']);
                                        $isStudentChoice = in_array($studentNorm, [$tfNorm, $tfNorm === 'verdadero' ? 'true' : 'false']);
                                        $isWrongChoice   = $isStudentChoice && !$isCorrectOption;
                                    @endphp
                                    <div class="option-item {{ $isCorrectOption ? 'is-correct' : '' }} {{ $isWrongChoice ? 'is-student-wrong' : '' }}">
                                        <span class="option-dot"></span>
                                        <span class="option-text">{{ $tfOption }}</span>
                                        @if($isStudentChoice && $isCorrectOption)
                                            <span class="option-student-tag">Tu respuesta ✓</span>
                                        @elseif($isWrongChoice)
                                            <span class="option-student-tag">Tu respuesta</span>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                            @php $feedbackText = $answer->is_correct ? $q->feedback_correct : $q->feedback_incorrect; @endphp
                            @if($feedbackText)
                                <div class="question-feedback-box">{{ $feedbackText }}</div>
                            @endif

                        {{-- PREGUNTA ABIERTA --}}
                        @elseif($type === 'OPEN_ENDED')
                            <div class="student-open-answer">{{ $answer->given_answer ?? '—' }}</div>
                            <div class="open-grading">
                                <div class="open-grading-label">Puntuación obtenida</div>
                                @if($answer->score !== null)
                                    <span class="q-status {{ $statusClass }}">
                                        {{ number_format((float) $answer->score, 1) }} / {{ number_format($maxPoints, 0) }} pts
                                    </span>
                                @else
                                    <span class="q-status pending">Pendiente de corrección</span>
                                @endif
                                @if($answer->feedback)
                                    <div class="open-grading-label">Comentario del profesor</div>
                                    <div class="question-feedback-box">{{ $answer->feedback }}</div>
                                @endif
                            </div>
                        @endif

                    </div>
                </div>

            @endforeach
        </div>
    @endif

    {{-- ===== FEEDBACK GENERAL ===== --}}
    @if($attempt->general_feedback)
        <div class="general-feedback-section">
            <h3>
                <i data-lucide="message-square"></i>
                Feedback general del profesor
            </h3>
            <div class="general-feedback-textarea">{{ $attempt->general_feedback }}</div>
        </div>
    @endif

</x-layouts.app>
