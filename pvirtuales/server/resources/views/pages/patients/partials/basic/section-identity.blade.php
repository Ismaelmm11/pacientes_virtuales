{{--
|--------------------------------------------------------------------------
| Sección 2: Identidad del Paciente
|--------------------------------------------------------------------------
--}}
<div class="cp-section" id="section-identity">

    <div class="cp-section-header">
        <div class="cp-section-icon">
            <i data-lucide="user-round"></i>
        </div>
        <h2 class="cp-section-title">Identidad del Paciente</h2>
    </div>
    <p class="cp-section-desc">¿Quién habla con el médico? Define los datos de la persona que asiste a la consulta.</p>

    {{-- Selector: paciente o acompañante --}}
    <div class="cp-form-group">
        <div class="cp-label-row">
            <label>¿Quién habla con el médico? <span class="required">*</span></label>
            <span class="help-tooltip">
                <span class="help-tooltip-icon">?</span>
                <span class="help-tooltip-bubble">
                    <strong>¿Para qué sirve?</strong>
                    Define quién es el personaje que habla en la conversación.
                    <div class="example">📝 "El propio paciente" → La IA simula al paciente</div>
                    <div class="example">📝 "Un acompañante" → La IA simula al acompañante</div>
                </span>
            </span>
        </div>
        <div class="cp-attendee-selector">
            <div class="cp-attendee-option">
                <input type="radio" name="attendee_type" id="attendee_patient" value="patient"
                       {{ old('attendee_type', 'patient') == 'patient' ? 'checked' : '' }}>
                <label for="attendee_patient">
                    <span class="attendee-icon">🧑‍⚕️</span>
                    <span class="attendee-label">El propio paciente</span>
                    <span class="attendee-desc">El paciente habla directamente con el médico</span>
                </label>
            </div>
            <div class="cp-attendee-option">
                <input type="radio" name="attendee_type" id="attendee_companion" value="companion"
                       {{ old('attendee_type') == 'companion' ? 'checked' : '' }}>
                <label for="attendee_companion">
                    <span class="attendee-icon">👨‍👩‍👧</span>
                    <span class="attendee-label">Un acompañante</span>
                    <span class="attendee-desc">Un familiar o cuidador habla en nombre del paciente</span>
                </label>
            </div>
        </div>
    </div>

    {{-- Campos del acompañante --}}
    <div class="cp-companion-fields {{ old('attendee_type') == 'companion' ? 'visible' : '' }}" id="companionFields">
        <p class="cp-companion-label">👤 Datos del acompañante</p>
        <div class="cp-form-row">
            <div class="cp-form-group">
                <div class="cp-label-row">
                    <label for="companion_name">Nombre <span class="required">*</span></label>
                    <span class="help-tooltip">
                        <span class="help-tooltip-icon">?</span>
                        <span class="help-tooltip-bubble">
                            <strong>¿Para qué sirve?</strong>
                            El nombre de quien habla con el médico. La IA lo usará para presentarse.
                        </span>
                    </span>
                </div>
                <input type="text" id="companion_name" name="companion_name"
                       value="{{ old('companion_name') }}" placeholder="Ej: Juana Pérez">
            </div>
            <div class="cp-form-group">
                <div class="cp-label-row">
                    <label for="companion_age">Edad</label>
                </div>
                <input type="number" id="companion_age" name="companion_age"
                       value="{{ old('companion_age') }}" min="14" max="100" placeholder="Ej: 38">
            </div>
        </div>
        <div class="cp-form-row">
            <div class="cp-form-group">
                <div class="cp-label-row">
                    <label for="companion_gender">Género</label>
                </div>
                <select id="companion_gender" name="companion_gender">
                    <option value="">Selecciona...</option>
                    <option value="masculino" {{ old('companion_gender') == 'masculino' ? 'selected' : '' }}>Masculino</option>
                    <option value="femenino"  {{ old('companion_gender') == 'femenino'  ? 'selected' : '' }}>Femenino</option>
                    <option value="otro"      {{ old('companion_gender') == 'otro'      ? 'selected' : '' }}>Otro</option>
                </select>
            </div>
            <div class="cp-form-group">
                <div class="cp-label-row">
                    <label for="companion_relation">Relación con el paciente <span class="required">*</span></label>
                </div>
                <select id="companion_relation" name="companion_relation">
                    <option value="">Selecciona...</option>
                    <option value="madre"      {{ old('companion_relation') == 'madre'      ? 'selected' : '' }}>Madre</option>
                    <option value="padre"      {{ old('companion_relation') == 'padre'      ? 'selected' : '' }}>Padre</option>
                    <option value="hijo_a"     {{ old('companion_relation') == 'hijo_a'     ? 'selected' : '' }}>Hijo/a</option>
                    <option value="pareja"     {{ old('companion_relation') == 'pareja'     ? 'selected' : '' }}>Pareja</option>
                    <option value="amigo_a"    {{ old('companion_relation') == 'amigo_a'    ? 'selected' : '' }}>Amigo/a</option>
                    <option value="cuidador_a" {{ old('companion_relation') == 'cuidador_a' ? 'selected' : '' }}>Cuidador/a</option>
                    <option value="otro"       {{ old('companion_relation') == 'otro'       ? 'selected' : '' }}>Otro</option>
                </select>
            </div>
        </div>
    </div>

    {{-- Datos del paciente (siempre visibles) --}}
    <div class="cp-form-row cp-form-row-3">
        <div class="cp-form-group">
            <div class="cp-label-row">
                <label for="patient_name">Nombre del paciente <span class="required">*</span></label>
                <span class="help-tooltip">
                    <span class="help-tooltip-icon">?</span>
                    <span class="help-tooltip-bubble">
                        <strong>¿Para qué sirve?</strong>
                        El nombre que usará la IA para referirse al paciente. Si hay acompañante, es la persona a la que trae.
                    </span>
                </span>
            </div>
            <input type="text" id="patient_name" name="patient_name"
                   value="{{ old('patient_name') }}" placeholder="Ej: María García" required>
        </div>
        <div class="cp-form-group">
            <div class="cp-label-row">
                <label for="patient_age">Edad <span class="required">*</span></label>
            </div>
            <input type="number" id="patient_age" name="patient_age"
                   value="{{ old('patient_age') }}" min="0" max="120" placeholder="Ej: 65" required>
        </div>
        <div class="cp-form-group">
            <div class="cp-label-row">
                <label for="patient_gender">Género <span class="required">*</span></label>
                <span class="help-tooltip">
                    <span class="help-tooltip-icon">?</span>
                    <span class="help-tooltip-bubble">
                        <strong>¿Para qué sirve?</strong>
                        La IA adapta las concordancias gramaticales según el género.
                    </span>
                </span>
            </div>
            <select id="patient_gender" name="patient_gender" required>
                <option value="">Selecciona...</option>
                <option value="masculino" {{ old('patient_gender') == 'masculino' ? 'selected' : '' }}>Masculino</option>
                <option value="femenino"  {{ old('patient_gender') == 'femenino'  ? 'selected' : '' }}>Femenino</option>
                <option value="otro"      {{ old('patient_gender') == 'otro'      ? 'selected' : '' }}>Otro</option>
            </select>
        </div>
    </div>

    <div class="cp-form-row">
        <div class="cp-form-group">
            <div class="cp-label-row">
                <label for="occupation">Ocupación <span class="hint">(opcional)</span></label>
                <span class="help-tooltip">
                    <span class="help-tooltip-icon">?</span>
                    <span class="help-tooltip-bubble">
                        <strong>¿Para qué sirve?</strong>
                        Afecta al contexto y vocabulario del personaje.
                        <div class="example">📝 "Camionero autónomo de rutas internacionales"</div>
                        <div class="example">📝 "Jubilada, antes trabajaba en comercio"</div>
                    </span>
                </span>
            </div>
            <input type="text" id="occupation" name="occupation"
                   value="{{ old('occupation') }}" placeholder="Ej: Jubilada, antes en comercio">
        </div>
        <div class="cp-form-group">
            <div class="cp-label-row">
                <label for="education_level">Nivel Educativo <span class="hint">(opcional)</span></label>
                <span class="help-tooltip">
                    <span class="help-tooltip-icon">?</span>
                    <span class="help-tooltip-bubble">
                        <strong>¿Para qué sirve?</strong>
                        Determina el vocabulario y la forma de expresarse del paciente.
                    </span>
                </span>
            </div>
            <select id="education_level" name="education_level">
                <option value="">Selecciona...</option>
                <option value="sin_estudios"  {{ old('education_level') == 'sin_estudios'  ? 'selected' : '' }}>Sin estudios formales</option>
                <option value="primaria"      {{ old('education_level') == 'primaria'      ? 'selected' : '' }}>Educación primaria</option>
                <option value="secundaria"    {{ old('education_level') == 'secundaria'    ? 'selected' : '' }}>Educación secundaria</option>
                <option value="bachillerato"  {{ old('education_level') == 'bachillerato'  ? 'selected' : '' }}>Bachillerato</option>
                <option value="universitario" {{ old('education_level') == 'universitario' ? 'selected' : '' }}>Universitario</option>
                <option value="postgrado"     {{ old('education_level') == 'postgrado'     ? 'selected' : '' }}>Postgrado</option>
            </select>
        </div>
    </div>

    <div class="cp-form-group">
        <div class="cp-label-row">
            <label for="personal_context">Contexto Personal <span class="hint">(opcional)</span></label>
            <span class="help-tooltip">
                <span class="help-tooltip-icon">?</span>
                <span class="help-tooltip-bubble">
                    <strong>¿Para qué sirve?</strong>
                    Situación familiar, económica y social del paciente. Le da profundidad al personaje.
                    <div class="example">📝 "Vive sola desde que enviudó hace 2 años. Su hija la visita los fines de semana."</div>
                    <div class="example">📝 "Ahogado económicamente, entre la hipoteca y el camión no llega a fin de mes."</div>
                </span>
            </span>
        </div>
        <textarea id="personal_context" name="personal_context"
                  placeholder="Ej: Vive sola desde que enviudó hace 2 años. Su hija la visita los fines de semana.">{{ old('personal_context') }}</textarea>
    </div>

</div>