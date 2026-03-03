{{-- Paso 2: Identidad del Paciente (Avanzado) --}}
<div class="section">
    <h2 class="section-title">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
            <circle cx="12" cy="7" r="4"/>
        </svg>
        Identidad del Paciente
    </h2>
    <p class="section-description">¿Quién habla con el médico? Define todos los datos de quien asiste a la consulta.</p>

    {{-- Selector: paciente o acompañante (reutiliza mismo HTML que básico) --}}
    <div class="form-group">
        <label>
            ¿Quién habla con el médico? <span class="required">*</span>
            <span class="help-tooltip">
                <span class="help-tooltip-icon">?</span>
                <span class="help-tooltip-bubble">
                    <strong>¿Para qué sirve?</strong>
                    Si es un acompañante, la IA simulará a esa persona hablando en nombre del paciente. El acompañante sabe toda la información del paciente.
                    <div class="example">📝 "Un acompañante" → Madre que trae a su hijo de 2 años a urgencias</div>
                </span>
            </span>
        </label>
        <div class="attendee-selector">
            <div class="attendee-option">
                <input type="radio" name="attendee_type" id="attendee_patient" value="patient"
                       {{ old('attendee_type', 'patient') == 'patient' ? 'checked' : '' }}>
                <label for="attendee_patient">
                    <span class="attendee-icon">🧑‍⚕️</span>
                    <span class="attendee-label">El propio paciente</span>
                    <span class="attendee-desc">El paciente habla directamente</span>
                </label>
            </div>
            <div class="attendee-option">
                <input type="radio" name="attendee_type" id="attendee_companion" value="companion"
                       {{ old('attendee_type') == 'companion' ? 'checked' : '' }}>
                <label for="attendee_companion">
                    <span class="attendee-icon">👨‍👩‍👧</span>
                    <span class="attendee-label">Un acompañante</span>
                    <span class="attendee-desc">Familiar o cuidador habla en su nombre</span>
                </label>
            </div>
        </div>
    </div>

    {{-- Campos del acompañante --}}
    <div class="companion-fields {{ old('attendee_type') == 'companion' ? 'visible' : '' }}" id="companionFields">
        <p class="companion-label">👤 Datos del acompañante (quien habla con el médico)</p>
        <div class="form-row">
            <div class="form-group">
                <label for="companion_name">
                    Nombre <span class="required">*</span>
                    <span class="help-tooltip">
                        <span class="help-tooltip-icon">?</span>
                        <span class="help-tooltip-bubble">
                            <strong>¿Para qué sirve?</strong>
                            El nombre de quien habla con el médico. La IA lo usará para presentarse.
                            <div class="example">📝 "Juana Pérez" (madre del paciente)</div>
                        </span>
                    </span>
                </label>
                <input type="text" id="companion_name" name="companion_name" value="{{ old('companion_name') }}" placeholder="Ej: Juana Pérez">
            </div>
            <div class="form-group">
                <label for="companion_age">
                    Edad
                    <span class="help-tooltip">
                        <span class="help-tooltip-icon">?</span>
                        <span class="help-tooltip-bubble">
                            <strong>¿Para qué sirve?</strong>
                            La edad del acompañante influye en cómo se comunica con el médico.
                            <div class="example">📝 28 (madre joven), 72 (abuela)</div>
                        </span>
                    </span>
                </label>
                <input type="number" id="companion_age" name="companion_age" value="{{ old('companion_age') }}" min="14" max="100" placeholder="Ej: 30">
            </div>
            <div class="form-group">
                <label for="companion_gender">
                    Género
                    <span class="help-tooltip">
                        <span class="help-tooltip-icon">?</span>
                        <span class="help-tooltip-bubble">
                            <strong>¿Para qué sirve?</strong>
                            La IA adapta las concordancias gramaticales según el género del acompañante.
                        </span>
                    </span>
                </label>
                <select id="companion_gender" name="companion_gender">
                    <option value="">Selecciona...</option>
                    <option value="masculino" {{ old('companion_gender') == 'masculino' ? 'selected' : '' }}>Masculino</option>
                    <option value="femenino" {{ old('companion_gender') == 'femenino' ? 'selected' : '' }}>Femenino</option>
                    <option value="otro" {{ old('companion_gender') == 'otro' ? 'selected' : '' }}>Otro</option>
                </select>
            </div>
        </div>
        <div class="form-group">
            <label for="companion_relation">
                Relación con el paciente <span class="required">*</span>
                <span class="help-tooltip">
                    <span class="help-tooltip-icon">?</span>
                    <span class="help-tooltip-bubble">
                        <strong>¿Para qué sirve?</strong>
                        Define el vínculo. La IA lo usará para determinar cuánto sabe del paciente y cómo se refiere a él/ella.
                        <div class="example">📝 "Madre" → "Vengo con mi hijo Pablo"</div>
                        <div class="example">📝 "Pareja" → "Le he traído porque yo le he notado que..."</div>
                    </span>
                </span>
            </label>
            <select id="companion_relation" name="companion_relation">
                <option value="">Selecciona...</option>
                <option value="madre" {{ old('companion_relation') == 'madre' ? 'selected' : '' }}>Madre</option>
                <option value="padre" {{ old('companion_relation') == 'padre' ? 'selected' : '' }}>Padre</option>
                <option value="hijo_a" {{ old('companion_relation') == 'hijo_a' ? 'selected' : '' }}>Hijo/a</option>
                <option value="pareja" {{ old('companion_relation') == 'pareja' ? 'selected' : '' }}>Pareja</option>
                <option value="amigo_a" {{ old('companion_relation') == 'amigo_a' ? 'selected' : '' }}>Amigo/a</option>
                <option value="cuidador_a" {{ old('companion_relation') == 'cuidador_a' ? 'selected' : '' }}>Cuidador/a</option>
                <option value="otro" {{ old('companion_relation') == 'otro' ? 'selected' : '' }}>Otro</option>
            </select>
        </div>
    </div>

    {{-- Datos del paciente --}}
    <div class="form-row">
        <div class="form-group">
            <label for="patient_name">
                Nombre del paciente <span class="required">*</span>
                <span class="help-tooltip">
                    <span class="help-tooltip-icon">?</span>
                    <span class="help-tooltip-bubble">
                        <strong>¿Para qué sirve?</strong>
                        El nombre que usará la IA. Si hay acompañante, es el nombre de la persona a la que trae.
                        <div class="example">📝 "Roberto Fernández" o "Pablo" (niño pequeño)</div>
                    </span>
                </span>
            </label>
            <input type="text" id="patient_name" name="patient_name" value="{{ old('patient_name') }}" placeholder="Ej: Roberto Fernández" required>
        </div>
        <div class="form-group">
            <label for="patient_age">
                Edad <span class="required">*</span>
                <span class="help-tooltip">
                    <span class="help-tooltip-icon">?</span>
                    <span class="help-tooltip-bubble">
                        <strong>¿Para qué sirve?</strong>
                        La edad afecta al vocabulario, diagnósticos probables y cómo se dirige al médico.
                        <div class="example">📝 2 (lactante), 54 (adulto), 82 (anciano)</div>
                    </span>
                </span>
            </label>
            <input type="number" id="patient_age" name="patient_age" value="{{ old('patient_age') }}" min="0" max="120" placeholder="Ej: 54" required>
        </div>
        <div class="form-group">
            <label for="patient_gender">
                Género <span class="required">*</span>
                <span class="help-tooltip">
                    <span class="help-tooltip-icon">?</span>
                    <span class="help-tooltip-bubble">
                        <strong>¿Para qué sirve?</strong>
                        La IA adapta concordancias gramaticales y contexto clínico según el género.
                    </span>
                </span>
            </label>
            <select id="patient_gender" name="patient_gender" required>
                <option value="">Selecciona...</option>
                <option value="masculino" {{ old('patient_gender') == 'masculino' ? 'selected' : '' }}>Masculino</option>
                <option value="femenino" {{ old('patient_gender') == 'femenino' ? 'selected' : '' }}>Femenino</option>
                <option value="otro" {{ old('patient_gender') == 'otro' ? 'selected' : '' }}>Otro</option>
            </select>
        </div>
    </div>

    <div class="form-row">
        <div class="form-group">
            <label for="occupation">
                Ocupación <span class="hint">(opcional)</span>
                <span class="help-tooltip">
                    <span class="help-tooltip-icon">?</span>
                    <span class="help-tooltip-bubble">
                        <strong>¿Para qué sirve?</strong>
                        La ocupación afecta al contexto del paciente. Un camionero no reacciona igual que un profesor.
                        <div class="example">📝 "Camionero autónomo de rutas internacionales"</div>
                        <div class="example">📝 "Administrativa en empresa de logística"</div>
                    </span>
                </span>
            </label>
            <input type="text" id="occupation" name="occupation" value="{{ old('occupation') }}" placeholder="Ej: Camionero autónomo de rutas internacionales">
        </div>
        <div class="form-group">
            <label for="education_level">
                Nivel Educativo <span class="hint">(opcional)</span>
                <span class="help-tooltip">
                    <span class="help-tooltip-icon">?</span>
                    <span class="help-tooltip-bubble">
                        <strong>¿Para qué sirve?</strong>
                        Determina el vocabulario del paciente. Sin estudios: "me duele aquí". Universitario: "tengo molestias lumbares".
                    </span>
                </span>
            </label>
            <select id="education_level" name="education_level">
                <option value="">Selecciona...</option>
                <option value="sin_estudios" {{ old('education_level') == 'sin_estudios' ? 'selected' : '' }}>Sin estudios formales</option>
                <option value="primaria" {{ old('education_level') == 'primaria' ? 'selected' : '' }}>Educación primaria</option>
                <option value="secundaria" {{ old('education_level') == 'secundaria' ? 'selected' : '' }}>Educación secundaria</option>
                <option value="bachillerato" {{ old('education_level') == 'bachillerato' ? 'selected' : '' }}>Bachillerato</option>
                <option value="universitario" {{ old('education_level') == 'universitario' ? 'selected' : '' }}>Universitario</option>
                <option value="postgrado" {{ old('education_level') == 'postgrado' ? 'selected' : '' }}>Postgrado</option>
            </select>
        </div>
    </div>

    {{-- Contexto sociolaboral (avanzado: más espacio) --}}
    <div class="form-group">
        <label for="personal_context">
            Contexto Sociolaboral y Personal
            <span class="help-tooltip">
                <span class="help-tooltip-icon">?</span>
                <span class="help-tooltip-bubble">
                    <strong>¿Para qué sirve?</strong>
                    El backstory completo del personaje. Situación laboral, familiar, económica. Todo lo que afecta a cómo vive su enfermedad y cómo se comporta en consulta.
                    <div class="example">📝 "Camionero autónomo de rutas internacionales. Hipoteca + letra del camión. Si para de trabajar, lo pierde todo. Su mujer Lourdes le ha obligado a venir."</div>
                    <div class="example">📝 "Administrativa en empresa de logística. Jefe déspota, ambiente laboral tóxico. Necesita la baja desesperadamente."</div>
                </span>
            </span>
        </label>
        <textarea id="personal_context" name="personal_context" class="textarea-large"
                  placeholder="Ej: Camionero autónomo. Hipoteca + letra del camión. Si para de trabajar, lo pierde todo.">{{ old('personal_context') }}</textarea>
    </div>

    {{-- Nivel de conocimiento médico (slider, obligatorio) --}}
    <div class="slider-group">
        <div class="slider-header">
            <label for="medical_knowledge">
                Conocimiento Médico del Paciente <span class="required">*</span>
                <span class="help-tooltip">
                    <span class="help-tooltip-icon">?</span>
                    <span class="help-tooltip-bubble">
                        <strong>¿Para qué sirve?</strong>
                        Determina el vocabulario que usa el paciente para describir síntomas. En modo avanzado puedes editar el texto generado en el paso de Psicología.
                        <div class="example">📝 Nivel 4: "Ha leído en Google sobre ciática y usa los términos correctamente"</div>
                    </span>
                </span>
            </label>
            <span class="slider-value" id="knowledgeValue">Básico</span>
        </div>
        <input type="range" id="medical_knowledge" name="medical_knowledge" min="1" max="5" value="{{ old('medical_knowledge', 2) }}">
        <div class="slider-labels">
            <span>Ninguno</span>
            <span>Profesional sanitario</span>
        </div>
        <div class="slider-preview" id="knowledgePreview"></div>
    </div>
</div>