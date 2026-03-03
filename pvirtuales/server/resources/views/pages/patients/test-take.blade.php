{{--
|--------------------------------------------------------------------------
| Test de Evaluación — Vista del Estudiante
|--------------------------------------------------------------------------
|
| Se muestra tras finalizar la simulación. El estudiante responde las
| preguntas creadas por el profesor para evaluar su razonamiento clínico.
|
| De momento las respuestas NO se guardan en base de datos.
| Se corrigen en el cliente (JS) para dar feedback inmediato.
|
--}}
<x-layouts.app title="Test — {{ $patient->case_title }}">
    <x-slot:styles>
        <link href="{{ asset('css/patient-test-take.css') }}" rel="stylesheet">
    </x-slot:styles>

    <div class="test-take-wrapper">

        {{-- Cabecera --}}
        <div class="test-take-header">
            <h1>📝 Test de Evaluación</h1>
            <p class="test-take-case">{{ $patient->case_title }}</p>
            <p class="test-take-info">Responde a las siguientes preguntas basándote en la consulta que acabas de realizar.</p>
        </div>

        {{-- Preguntas --}}
        @if($questions->isEmpty())
            <div class="test-empty">
                <p>Este paciente no tiene test de evaluación configurado.</p>
                <a href="{{ route('home') }}" class="btn-back-home">Volver al Inicio</a>
            </div>
        @else
            <div class="test-questions" id="testQuestions">
                @foreach($questions as $index => $question)
                    <div class="tq-card" data-question-id="{{ $question->id }}" data-type="{{ $question->question_type }}">
                        <div class="tq-header">
                            <span class="tq-number">{{ $index + 1 }}</span>
                            <span class="tq-type">
                                @switch($question->question_type)
                                    @case('MULTIPLE_CHOICE') Opción Múltiple @break
                                    @case('TRUE_FALSE') Verdadero / Falso @break
                                    @case('OPEN_ENDED') Pregunta Abierta @break
                                @endswitch
                            </span>
                            <span class="tq-points">{{ $question->points }} pts</span>
                        </div>

                        <p class="tq-text">{{ $question->question_text }}</p>

                        {{-- OPCIÓN MÚLTIPLE --}}
                        @if($question->question_type === 'MULTIPLE_CHOICE' && $question->options)
                            <div class="tq-options">
                                @foreach($question->options as $optIndex => $option)
                                    <label class="tq-option" data-value="{{ $option }}">
                                        <input type="radio" name="q_{{ $question->id }}" value="{{ $option }}">
                                        <span class="tq-option-letter">{{ chr(65 + $optIndex) }}</span>
                                        <span class="tq-option-text">{{ $option }}</span>
                                    </label>
                                @endforeach
                            </div>
                            <input type="hidden" class="correct-answer" value="{{ $question->correct_answer }}">
                            <input type="hidden" class="feedback-correct" value="{{ $question->feedback_correct }}">
                            <input type="hidden" class="feedback-incorrect" value="{{ $question->feedback_incorrect }}">
                        @endif

                        {{-- VERDADERO / FALSO --}}
                        @if($question->question_type === 'TRUE_FALSE')
                            <div class="tq-options tq-tf">
                                <label class="tq-option" data-value="true">
                                    <input type="radio" name="q_{{ $question->id }}" value="true">
                                    <span class="tq-option-text">Verdadero</span>
                                </label>
                                <label class="tq-option" data-value="false">
                                    <input type="radio" name="q_{{ $question->id }}" value="false">
                                    <span class="tq-option-text">Falso</span>
                                </label>
                            </div>
                            <input type="hidden" class="correct-answer" value="{{ $question->correct_answer }}">
                            <input type="hidden" class="feedback-correct" value="{{ $question->feedback_correct }}">
                            <input type="hidden" class="feedback-incorrect" value="{{ $question->feedback_incorrect }}">
                        @endif

                        {{-- PREGUNTA ABIERTA --}}
                        @if($question->question_type === 'OPEN_ENDED')
                            <textarea class="tq-open-answer" name="q_{{ $question->id }}"
                                      placeholder="Escribe tu respuesta aquí..."></textarea>
                        @endif

                        {{-- Zona de feedback (oculta hasta corregir) --}}
                        <div class="tq-feedback" style="display: none;"></div>
                    </div>
                @endforeach
            </div>

            {{-- Botón de corregir --}}
            <div class="test-actions">
                <button type="button" class="btn-correct" id="btnCorrect" onclick="correctTest()">
                    Corregir Test
                </button>
            </div>

            {{-- Resultado global (oculto hasta corregir) --}}
            <div class="test-result" id="testResult" style="display: none;">
                <div class="result-score">
                    <span class="result-label">Tu puntuación</span>
                    <span class="result-value" id="resultScore">0</span>
                    <span class="result-total">/ {{ $questions->sum('points') }} puntos</span>
                </div>
                <a href="{{ route('home') }}" class="btn-back-home">Volver al Inicio</a>
            </div>
        @endif
    </div>

    <x-slot:scripts>
        <script src="{{ asset('js/patient-test-take.js') }}"></script>
    </x-slot:scripts>
</x-layouts.app>