{{--
|--------------------------------------------------------------------------
| Sección 1: Información del Caso
|--------------------------------------------------------------------------
--}}
<div class="cp-section" id="section-case">

    <div class="cp-section-header">
        <div class="cp-section-icon">
            <i data-lucide="file-text"></i>
        </div>
        <h2 class="cp-section-title">Información del Caso</h2>
    </div>
    <p class="cp-section-desc">Define el contexto educativo del caso clínico.</p>

    {{-- Nombre del paciente --}}
    <div class="cp-form-group">
        <div class="cp-label-row">
            <label for="case_title">Nombre del Paciente <span class="required">*</span></label>
            <span class="help-tooltip">
                <span class="help-tooltip-icon">?</span>
                <span class="help-tooltip-bubble">
                    <strong>¿Para qué sirve?</strong>
                    El nombre real del paciente. Aparecerá en el encabezado del prompt junto a la descripción.
                </span>
            </span>
        </div>
        <input type="text" id="case_title" name="case_title"
               value="{{ old('case_title') }}"
               placeholder="Ej: Roberto" required>
    </div>

    {{-- Descripción del caso --}}
    <div class="cp-form-group">
        <div class="cp-label-row">
            <label for="patient_description">Descripción del Caso <span class="required">*</span></label>
            <span class="help-tooltip">
                <span class="help-tooltip-icon">?</span>
                <span class="help-tooltip-bubble">
                    <strong>¿Para qué sirve?</strong>
                    Una frase corta que describe quién es el personaje. Se combina con el nombre para construir el
                    encabezado del prompt: <em>"Roberto, camionero estresado"</em>. Ayuda a la IA a entender de un vistazo la esencia del personaje.
                    <div class="example">📝 "Camionero estresado"</div>
                    <div class="example">📝 "Niño con fiebre alta"</div>
                    <div class="example">📝 "Paciente hipocondriaco"</div>
                </span>
            </span>
        </div>
        <input type="text" id="patient_description" name="patient_description"
               value="{{ old('patient_description') }}"
               placeholder="Ej: Camionero estresado">
    </div>

    {{-- Objetivos de aprendizaje --}}
    <div class="cp-form-group">
        <div class="cp-label-row">
            <label for="learning_objectives">Objetivos de Aprendizaje <span class="hint">(opcional)</span></label>
            <span class="help-tooltip">
                <span class="help-tooltip-icon">?</span>
                <span class="help-tooltip-bubble">
                    <strong>¿Para qué sirve?</strong>
                    Define qué competencias debe desarrollar el estudiante. La IA guiará sutilmente la conversación para
                    que estas áreas sean relevantes, sin romper el personaje.
                    <div class="example">📝 "Realizar anamnesis cardiovascular completa, identificar factores de riesgo y signos de alarma"</div>
                </span>
            </span>
        </div>
        <textarea id="learning_objectives" name="learning_objectives"
                  placeholder="Ej: Realizar anamnesis cardiovascular completa, identificar signos de alarma">{{ old('learning_objectives') }}</textarea>
    </div>

</div>