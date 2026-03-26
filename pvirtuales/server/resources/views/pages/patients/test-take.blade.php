{{--
|--------------------------------------------------------------------------
| Test de Evaluación — Vista del Alumno (una pregunta a la vez)
|--------------------------------------------------------------------------
|
| El alumno responde las preguntas de una en una con navegación prev/next.
| Soporta tres tipos: opción múltiple, verdadero/falso y pregunta abierta.
|
--}}
<x-layouts.app>

    <x-slot name="title">Cuestionario — {{ $patient->case_title }}</x-slot>

    <x-slot name="styles">
        <link href="{{ asset('css/dashboard.css') }}" rel="stylesheet">
        <link href="{{ asset('css/modal.css') }}" rel="stylesheet">
    </x-slot>

    <x-slot name="topbar">
        <div class="topbar">
            <div class="topbar-left">
                <div class="topbar-title">Cuestionario de Evaluación</div>
                <div class="topbar-subtitle">{{ $patient->case_title }}</div>
            </div>
        </div>
    </x-slot>

    <div class="dashboard-content">

        @if($questions->isEmpty())
            <div class="empty-state">
                <i data-lucide="clipboard-x"></i>
                <p>Este paciente no tiene preguntas de evaluación configuradas.</p>
                <a href="{{ route('student.patients.index') }}" class="btn btn-primary">Volver a Pacientes</a>
            </div>
        @else

            @php $totalQ = $questions->count(); @endphp

            <form action="{{ route('student.patients.test.submit', $patient) }}" method="POST" id="testForm">
                @csrf

                {{-- Barra de progreso superior --}}
                <div class="tt-progress-wrap">
                    <div class="tt-progress-fill" id="ttProgressFill"></div>
                </div>

                {{-- Cabecera: contador + flechas de navegación --}}
                <div class="tt-header">
                    <div class="tt-header-meta">
                        <span class="tt-subject">{{ $patient->case_title }}</span>
                        <div class="tt-question-count">
                            Pregunta <span id="ttCurrentNum">1</span> de {{ $totalQ }}
                            <span class="badge badge-secondary tt-pct-badge" id="ttPctBadge">
                                {{ round(100 / $totalQ) }}%
                            </span>
                        </div>
                    </div>
                    <div class="tt-nav-arrows">
                        <button type="button" class="tt-nav-btn" id="ttPrev" disabled>
                            <i data-lucide="chevron-left"></i>
                        </button>
                        <button type="button" class="tt-nav-btn" id="ttNext" {{ $totalQ <= 1 ? 'disabled' : '' }}>
                            <i data-lucide="chevron-right"></i>
                        </button>
                    </div>
                </div>

                {{-- Slides de preguntas --}}
                @foreach($questions as $index => $question)
                    <div class="tt-slide {{ $index === 0 ? 'tt-slide--active' : '' }}" data-index="{{ $index }}">

                        {{-- Tarjeta con el enunciado --}}
                        <div class="tt-question-card">
                            <div class="tt-question-badges">
                                <span class="badge badge-secondary">{{ $question->type_label }}</span>
                                @if($question->is_required)
                                    <span class="badge badge-warning">Obligatoria</span>
                                @endif
                            </div>
                            <p class="tt-question-text">{{ $question->question_text }}</p>
                        </div>

                        {{-- Opciones de respuesta --}}
                        @if($question->question_type === \App\Models\Question::TYPE_MULTIPLE_CHOICE)
                            <div class="tt-options">
                                @foreach($question->options as $optIndex => $option)
                                    @php $letter = chr(65 + $optIndex); @endphp
                                    <label class="tt-option">
                                        <input type="radio" name="answers[{{ $question->id }}]" value="{{ $option }}"
                                            class="tt-option-input">
                                        <span class="tt-option-letter">{{ $letter }}</span>
                                        <span class="tt-option-text">{{ $option }}</span>
                                        <span class="tt-option-check">
                                            <i data-lucide="check"></i>
                                        </span>
                                    </label>
                                @endforeach
                            </div>

                        @elseif($question->question_type === \App\Models\Question::TYPE_TRUE_FALSE)
                            <div class="tt-options tt-options--tf">
                                <label class="tt-option">
                                    <input type="radio" name="answers[{{ $question->id }}]" value="true" class="tt-option-input">
                                    <span class="tt-option-letter">V</span>
                                    <span class="tt-option-text">Verdadero</span>
                                    <span class="tt-option-check"><i data-lucide="check"></i></span>
                                </label>
                                <label class="tt-option">
                                    <input type="radio" name="answers[{{ $question->id }}]" value="false" class="tt-option-input">
                                    <span class="tt-option-letter">F</span>
                                    <span class="tt-option-text">Falso</span>
                                    <span class="tt-option-check"><i data-lucide="check"></i></span>
                                </label>
                            </div>

                        @else
                            {{-- Pregunta abierta --}}
                            <div class="tt-open-answer">
                                <textarea name="answers[{{ $question->id }}]" rows="5" class="tt-textarea"
                                    placeholder="Escribe tu respuesta aquí..."></textarea>
                            </div>
                        @endif

                    </div>
                @endforeach

                {{-- Pie con botón de envío (solo visible en la última pregunta) --}}
                <div class="tt-footer" id="ttFooter">
                    <button type="button" id="btnSubmitTest" class="btn btn-primary btn-lg tt-submit-btn">
                        <i data-lucide="send"></i>
                        Enviar Cuestionario
                    </button>

                </div>

            </form>
        @endif


        {{-- Modal de confirmación de envío --}}
        <div class="sim-modal-overlay" id="submitModal">
            <div class="sim-modal">
                <div class="sim-modal-icon">
                    <i data-lucide="send" style="width:26px;height:26px;"></i>
                </div>
                <div class="sim-modal-title">Enviar cuestionario</div>
                <div class="sim-modal-body">
                    ¿Estás seguro de que quieres enviar el cuestionario?<br>
                    <strong>No podrás modificar tus respuestas.</strong>
                </div>
                <div class="sim-modal-actions">
                    <button type="button" class="btn btn-ghost" id="btnCancelSubmit">Cancelar</button>
                    <button type="button" class="btn btn-primary" id="btnConfirmSubmit">
                        <i data-lucide="check"></i>
                        Confirmar envío
                    </button>
                </div>
            </div>
        </div>

        {{-- Modal de aviso: preguntas sin responder --}}
        <div class="sim-modal-overlay" id="incompleteModal">
            <div class="sim-modal">
                <div class="sim-modal-icon" style="background: rgba(231,76,60,0.12); color: var(--color-danger);">
                    <i data-lucide="alert-circle" style="width:26px;height:26px;"></i>
                </div>
                <div class="sim-modal-title">Preguntas sin responder</div>
                <div class="sim-modal-body">
                    Debes responder <strong>todas las preguntas</strong> antes de enviar el cuestionario.
                </div>
                <div class="sim-modal-actions">
                    <button type="button" class="btn btn-primary" id="btnCloseIncomplete">
                        Entendido
                    </button>
                </div>
            </div>
        </div>

    </div>

    <script>
        (function () {
            const totalQuestions = {{ $questions->count() }};
            if (totalQuestions === 0) return;

            let current = 0;

            const slides = document.querySelectorAll('.tt-slide');
            const prevBtn = document.getElementById('ttPrev');
            const nextBtn = document.getElementById('ttNext');
            const currentNum = document.getElementById('ttCurrentNum');
            const pctBadge = document.getElementById('ttPctBadge');
            const footer = document.getElementById('ttFooter');
            const progressFill = document.getElementById('ttProgressFill');

            function goTo(index) {
                slides[current].classList.remove('tt-slide--active');
                current = index;
                slides[current].classList.add('tt-slide--active');

                currentNum.textContent = current + 1;
                const pct = Math.round(((current + 1) / totalQuestions) * 100);
                pctBadge.textContent = pct + '%';
                progressFill.style.width = pct + '%';

                prevBtn.disabled = (current === 0);
                nextBtn.disabled = (current === totalQuestions - 1);

                if (current === totalQuestions - 1) {
                    footer.classList.add('tt-footer--visible');
                } else {
                    footer.classList.remove('tt-footer--visible');
                }

                if (typeof lucide !== 'undefined') lucide.createIcons();
            }

            prevBtn.addEventListener('click', () => { if (current > 0) goTo(current - 1); });
            nextBtn.addEventListener('click', () => { if (current < totalQuestions - 1) goTo(current + 1); });

            // Marcar opción seleccionada visualmente
            document.querySelectorAll('.tt-option-input').forEach(function (input) {
                input.addEventListener('change', function () {
                    var name = this.name;
                    document.querySelectorAll('input[name="' + name + '"]').forEach(function (i) {
                        i.closest('.tt-option').classList.remove('tt-option--selected');
                    });
                    this.closest('.tt-option').classList.add('tt-option--selected');
                });
            });

            goTo(0);
        })();

        // Validar que todas las preguntas están respondidas
        function getPrimeraNoRespondida() {
            const slides = document.querySelectorAll('.tt-slide');
            const totalQuestions = {{ $questions->count() }};
            document.querySelectorAll('.tt-question-card--error').forEach(el => {
                el.classList.remove('tt-question-card--error');
            });
            for (let i = 0; i < totalQuestions; i++) {
                const slide = slides[i];
                const textarea = slide.querySelector('.tt-textarea');
                if (textarea) {
                    if (textarea.value.trim() === '') return i;
                } else {
                    if (!slide.querySelector('.tt-option-input:checked')) return i;
                }
            }
            return -1;
        }

        // Modal de confirmación de envío
        const submitModal = document.getElementById('submitModal');
        const btnSubmit = document.getElementById('btnSubmitTest');
        const btnCancel = document.getElementById('btnCancelSubmit');
        const btnConfirm = document.getElementById('btnConfirmSubmit');
        const testForm = document.getElementById('testForm');


        btnCancel.addEventListener('click', () => {
            submitModal.classList.remove('active');
        });

        btnConfirm.addEventListener('click', () => {
            testForm.submit();
        });

        // Cerrar al hacer clic en el fondo
        submitModal.addEventListener('click', (e) => {
            if (e.target === submitModal) submitModal.classList.remove('active');
        });

        btnSubmit.addEventListener('click', () => {
            const noRespondida = getPrimeraNoRespondida();
            if (noRespondida !== -1) {
                incompleteModal.classList.add('active');
                if (typeof lucide !== 'undefined') lucide.createIcons();
                return;
            }
            submitModal.classList.add('active');
            if (typeof lucide !== 'undefined') lucide.createIcons();
        });

        const incompleteModal = document.getElementById('incompleteModal');
        const btnCloseIncomplete = document.getElementById('btnCloseIncomplete');

        btnCloseIncomplete.addEventListener('click', () => incompleteModal.classList.remove('active'));
        incompleteModal.addEventListener('click', (e) => {
            if (e.target === incompleteModal) incompleteModal.classList.remove('active');
        });



    </script>

</x-layouts.app>