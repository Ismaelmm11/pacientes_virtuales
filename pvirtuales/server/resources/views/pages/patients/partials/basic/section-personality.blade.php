{{--
|--------------------------------------------------------------------------
| Sección 4: Personalidad y Comportamiento
|--------------------------------------------------------------------------
--}}
<div class="cp-section" id="section-personality">

    <div class="cp-section-header">
        <div class="cp-section-icon">
            <i data-lucide="smile"></i>
        </div>
        <h2 class="cp-section-title">Personalidad y Comportamiento</h2>
    </div>
    <p class="cp-section-desc">Define cómo se comporta y comunica el paciente durante la consulta.</p>

    {{-- Tipo de personalidad --}}
    <div class="cp-form-group">
        <div class="cp-label-row">
            <label>Tipo de Personalidad <span class="required">*</span></label>
            <span class="help-tooltip">
                <span class="help-tooltip-icon">?</span>
                <span class="help-tooltip-bubble">
                    <strong>¿Para qué sirve?</strong>
                    Define el estado emocional dominante del paciente durante toda la consulta. Al seleccionar una personalidad, se muestra un texto explicando la personalidad seleccionada,
                    además es posible personalizar ese texto para afinar aún más el comportamiento del paciente.
                </span>
            </span>
        </div>

        @php
            $personalities = [
                'colaborador'   => '😊 Colaborador',
                'ansioso'       => '😰 Ansioso',
                'reservado'     => '🤐 Reservado',
                'demandante'    => '😤 Demandante',
                'minimizador'   => '🙄 Minimizador',
                'hipocondriaco' => '😱 Hipocondríaco',
                'agresivo'      => '😡 Agresivo',
                'deprimido'     => '😞 Deprimido',
                'desconfiado'   => '🤨 Desconfiado',
                'confuso'       => '😵‍💫 Confuso',
                'evasivo'       => '😶 Evasivo',
            ];
        @endphp

        <div class="cp-personality-grid">
            @foreach($personalities as $value => $label)
                <div class="cp-personality-option">
                    <input type="radio" name="personality_type" id="personality_{{ $value }}"
                           value="{{ $value }}" {{ old('personality_type') == $value ? 'checked' : '' }} required>
                    <label for="personality_{{ $value }}">{{ $label }}</label>
                </div>
            @endforeach
        </div>

        <div class="cp-preview-box" id="personalityPreview">
            <div class="cp-preview-label">Texto que se generará en el prompt:</div>
            <span id="personalityPreviewText"></span>
        </div>

        <span class="cp-custom-toggle" id="personalityCustomToggle">✏️ Quiero personalizar este texto</span>
        <div class="cp-custom-field" id="personalityCustomField">
            <textarea id="personalityCustomText" name="personality_custom"
                      placeholder="Escribe aquí tu descripción personalizada...">{{ old('personality_custom') }}</textarea>
        </div>
    </div>

    <hr class="cp-section-divider">

    {{-- Verbosidad --}}
    <div class="cp-slider-group">
        <div class="cp-slider-header">
            <div class="cp-slider-header-left">
                <label for="verbosity_level">Nivel de Verbosidad <span class="required">*</span></label>
                <span class="help-tooltip">
                    <span class="help-tooltip-icon">?</span>
                    <span class="help-tooltip-bubble">
                        <strong>¿Para qué sirve?</strong>
                        Controla cuánta información ofrece el paciente en cada respuesta. Cada nivel muestra el texto que describe el comportamiento del paciente para ese nivel, pero es posible personalizarlo para afinar aún más el comportamiento.
                    </span>
                </span>
            </div>
            <span class="cp-slider-value" id="verbosityValue">Normal</span>
        </div>
        <input type="range" id="verbosity_level" name="verbosity_level"
               min="1" max="5" value="{{ old('verbosity_level', 3) }}">
        <div class="cp-slider-labels">
            <span>Muy escueto</span>
            <span>Muy detallista</span>
        </div>
        <div class="cp-preview-box visible" id="verbosityPreview"></div>
        <span class="cp-custom-toggle" id="verbosityCustomToggle">✏️ Quiero personalizar este texto</span>
        <div class="cp-custom-field" id="verbosityCustomField">
            <textarea id="verbosityCustomText" name="verbosity_custom"
                      placeholder="Escribe aquí cómo quieres que se exprese el paciente...">{{ old('verbosity_custom') }}</textarea>
        </div>
    </div>

    {{-- Conocimiento médico --}}
    <div class="cp-slider-group">
        <div class="cp-slider-header">
            <div class="cp-slider-header-left">
                <label for="medical_knowledge">Conocimiento Médico <span class="required">*</span></label>
                <span class="help-tooltip">
                    <span class="help-tooltip-icon">?</span>
                    <span class="help-tooltip-bubble">
                        <strong>¿Para qué sirve?</strong>
                        Determina el vocabulario con el que el paciente describe sus síntomas, por cada nivel se muestra un texto que describe el tipo de lenguaje que usa el paciente, pero es posible personalizarlo para afinar aún más el comportamiento.
                    </span>
                </span>
            </div>
            <span class="cp-slider-value" id="knowledgeValue">Básico</span>
        </div>
        <input type="range" id="medical_knowledge" name="medical_knowledge"
               min="1" max="5" value="{{ old('medical_knowledge', 2) }}">
        <div class="cp-slider-labels">
            <span>Ninguno</span>
            <span>Profesional sanitario</span>
        </div>
        <div class="cp-preview-box visible" id="knowledgePreview"></div>
        <span class="cp-custom-toggle" id="knowledgeCustomToggle">✏️ Quiero personalizar este texto</span>
        <div class="cp-custom-field" id="knowledgeCustomField">
            <textarea id="knowledgeCustomText" name="knowledge_custom"
                      placeholder="Escribe aquí cómo quieres que use la terminología médica el paciente...">{{ old('knowledge_custom') }}</textarea>
        </div>
    </div>

    {{-- Preocupaciones --}}
    <div class="cp-form-group">
        <div class="cp-label-row">
            <label for="hidden_concerns">Preocupaciones del Paciente <span class="hint">(opcional)</span></label>
            <span class="help-tooltip">
                <span class="help-tooltip-icon">?</span>
                <span class="help-tooltip-bubble">
                    <strong>¿Para qué sirve?</strong>
                    Lo que le preocupa al paciente más allá de los síntomas.
                    <div class="example">📝 "Tiene miedo de que sea algo grave y no quiere preocupar a su familia"</div>
                    <div class="example">📝 "Está angustiada porque su padre murió de lo mismo"</div>
                </span>
            </span>
        </div>
        <textarea id="hidden_concerns" name="hidden_concerns"
                  placeholder="Ej: Le preocupa si podrá seguir trabajando. Teme que sea algo grave.">{{ old('hidden_concerns') }}</textarea>
    </div>

</div>