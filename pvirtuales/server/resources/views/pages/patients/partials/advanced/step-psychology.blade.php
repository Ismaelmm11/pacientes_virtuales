{{-- Paso 4: Psicología y Comportamiento (Avanzado) --}}
<div class="section">
    <h2 class="section-title">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <circle cx="12" cy="12" r="10"/>
            <path d="M8 14s1.5 2 4 2 4-2 4-2"/>
            <line x1="9" y1="9" x2="9.01" y2="9"/>
            <line x1="15" y1="9" x2="15.01" y2="9"/>
        </svg>
        Psicología y Comportamiento
    </h2>
    <p class="section-description">Define cómo se siente, actúa, habla y reacciona el paciente.</p>

    {{-- Personalidad con preview --}}
    <div class="form-group">
        <label>
            Tipo de Personalidad <span class="required">*</span>
            <span class="help-tooltip">
                <span class="help-tooltip-icon">?</span>
                <span class="help-tooltip-bubble">
                    <strong>¿Para qué sirve?</strong>
                    El tono emocional base. Personalízalo para algo específico como "manipulación calculada" o "ira defensiva".
                </span>
            </span>
        </label>
        <div class="personality-grid">
            @php
                $personalities = [
                    'colaborador' => '😊 Colaborador', 'ansioso' => '😰 Ansioso',
                    'reservado' => '🤐 Reservado', 'demandante' => '😤 Demandante',
                    'minimizador' => '🙄 Minimizador', 'hipocondriaco' => '😱 Hipocondríaco',
                    'agresivo' => '😡 Agresivo', 'deprimido' => '😞 Deprimido',
                    'desconfiado' => '🤨 Desconfiado', 'confuso' => '😵‍💫 Confuso',
                    'evasivo' => '😶 Evasivo',
                ];
            @endphp
            @foreach($personalities as $value => $label)
                <div class="personality-option">
                    <input type="radio" name="personality_type" id="personality_{{ $value }}"
                           value="{{ $value }}" {{ old('personality_type') == $value ? 'checked' : '' }} required>
                    <label for="personality_{{ $value }}">{{ $label }}</label>
                </div>
            @endforeach
        </div>
        <div class="personality-preview" id="personalityPreview">
            <div class="preview-label">Texto que se generará en el prompt:</div>
            <span id="personalityPreviewText"></span>
        </div>
        <span class="personality-custom-toggle" id="personalityCustomToggle">✏️ Quiero personalizar este texto</span>
        <div class="personality-custom" id="personalityCustomField" style="display: none;">
            <textarea id="personalityCustomText" name="personality_custom"
                      placeholder="Ej: MANIPULACIÓN CALCULADA. Representas un papel de víctima con objetivo claro: conseguir la baja.">{{ old('personality_custom') }}</textarea>
        </div>
    </div>

    {{-- Contexto emocional (SOLO avanzado) --}}
    <div class="form-group">
        <label for="emotional_context">
            ¿Por qué se siente así? <span class="hint">(opcional)</span>
            <span class="help-tooltip">
                <span class="help-tooltip-icon">?</span>
                <span class="help-tooltip-bubble">
                    <strong>¿Para qué sirve?</strong>
                    El porqué de la emoción. La IA necesita este contexto para mantener coherencia.
                    <div class="example">📝 "Tiene PÁNICO a que le retiren el carnet. Sin conducir, no cobra. Por eso ataca."</div>
                    <div class="example">📝 "Necesita la baja para ir a una boda. Interpreta un papel de víctima."</div>
                </span>
            </span>
        </label>
        <textarea id="emotional_context" name="emotional_context" class="textarea-large"
                  placeholder="Ej: Tiene pánico a que le retiren el carnet. Sin conducir, no cobra.">{{ old('emotional_context') }}</textarea>
    </div>

    {{-- Verbosidad --}}
    <div class="slider-group">
        <div class="slider-header">
            <label for="verbosity_level">
                Nivel de Verbosidad
                <span class="help-tooltip">
                    <span class="help-tooltip-icon">?</span>
                    <span class="help-tooltip-bubble">
                        <strong>¿Para qué sirve?</strong>
                        Controla la longitud de las respuestas del paciente. Nivel bajo: monosílabos. Nivel alto: divagaciones.
                        <div class="example">📝 Nivel 1: "Sí." "No." "Aquí."</div>
                        <div class="example">📝 Nivel 5: "Ay, pues verá, el lunes estaba yo en el Mercadona..."</div>
                    </span>
                </span>
            </label>
            <span class="slider-value" id="verbosityValue">Normal</span>
        </div>
        <input type="range" id="verbosity_level" name="verbosity_level" min="1" max="5" value="{{ old('verbosity_level', 3) }}">
        <div class="slider-labels"><span>Muy escueto</span><span>Muy detallista</span></div>
        <div class="slider-preview" id="verbosityPreview"></div>
    </div>

    {{-- Preocupaciones ocultas --}}
    <div class="form-group">
        <label for="hidden_concerns">
            Preocupaciones Ocultas <span class="hint">(opcional)</span>
            <span class="help-tooltip">
                <span class="help-tooltip-icon">?</span>
                <span class="help-tooltip-bubble">
                    <strong>¿Para qué sirve?</strong>
                    Lo que NO dice a menos que haya confianza o se pregunte directamente.
                    <div class="example">📝 "Pensó que se moría solo en la carretera. Le aterra que le quiten el carnet."</div>
                </span>
            </span>
        </label>
        <textarea id="hidden_concerns" name="hidden_concerns"
                  placeholder="Ej: Pensó que se moría solo en la carretera. Le aterra perder el carnet.">{{ old('hidden_concerns') }}</textarea>
    </div>

    {{-- Reglas de interacción (SOLO avanzado) --}}
    <div class="form-group">
        <label>
            Reglas de Interacción <span class="hint">(opcional)</span>
            <span class="help-tooltip">
                <span class="help-tooltip-icon">?</span>
                <span class="help-tooltip-bubble">
                    <strong>¿Para qué sirve?</strong>
                    Reglas condicionales: "Si el alumno hace X, el paciente reacciona con Y". Esto hace la simulación dinámica.
                    <div class="example">📝 Si: "Muestra empatía real" → Entonces: "Baja las defensas y revela el episodio del pecho"</div>
                    <div class="example">📝 Si: "Es frío o burocrático" → Entonces: "Se mantiene hostil hasta el final"</div>
                    <div class="example">📝 Si: "Le pregunta por el tabaco" → Entonces: "Se pone a la defensiva: '¿Ya estamos? ¿Ha venido a curarme o a darme un sermón?'"</div>
                </span>
            </span>
        </label>
        <div class="dynamic-list" id="rulesContainer"></div>
        <button type="button" class="btn-add-item" onclick="addRule()">
            <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            Añadir Regla
        </button>
    </div>
</div>