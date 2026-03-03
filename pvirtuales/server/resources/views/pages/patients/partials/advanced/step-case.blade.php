{{-- 
|--------------------------------------------------------------------------
| Paso 1: Información del Caso (Configuración de Control)
|--------------------------------------------------------------------------
|
| Este bloque establece no solo la temática, sino el nivel de autonomía 
| que tendrá la IA para generar información clínica nueva.
|
--}}
<div class="section">
    <h2 class="section-title">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
            <polyline points="14 2 14 8 20 8"/>
        </svg>
        Información del Caso
    </h2>
    <p class="section-description">Define el contexto educativo del caso clínico.</p>

    {{-- Título del caso: Identificador para el docente --}}
    <div class="form-group">
        <label for="case_title">
            Título del Caso <span class="required">*</span>
            <span class="help-tooltip">
                <span class="help-tooltip-icon">?</span>
                <span class="help-tooltip-bubble">
                    <strong>¿Para qué sirve?</strong>
                    Identifica el caso internamente. Permite diferenciar entre variantes del mismo cuadro.
                </span>
            </span>
        </label>
        <input type="text" id="case_title" name="case_title" value="{{ old('case_title') }}"
               placeholder="Ej: Paciente hostil que oculta síntomas cardíacos" required>
    </div>

    {{-- Especialidad: Define el dominio de conocimiento de la IA --}}
    <div class="form-group">
        <label for="specialty">
            Especialidad <span class="required">*</span>
        </label>
        <select id="specialty" name="specialty" required>
            <option value="">Selecciona...</option>
            {{-- Opciones de especialidad con persistencia old() --}}
            <option value="medicina_general" {{ old('specialty') == 'medicina_general' ? 'selected' : '' }}>Medicina General</option>
            <option value="cardiologia" {{ old('specialty') == 'cardiologia' ? 'selected' : '' }}>Cardiología</option>
            {{-- ... resto de opciones ... --}}
        </select>
    </div>

    {{-- 
        INTERRUPTOR DE ALUCINACIONES: 
        Diferencia clave del modo avanzado. Permite bloquear la capacidad de la IA 
        para inventar datos paramétricos (analíticas, constantes).
    --}}
    <div class="form-group">
        <label>
            ¿Puede la IA inventar datos médicos no definidos?
            <span class="help-tooltip">
                <span class="help-tooltip-icon">?</span>
                <span class="help-tooltip-bubble">
                    <strong>Importante:</strong>
                    Si se desactiva, el modelo evitará inventar laboratorios o signos 
                    físicos que no hayas especificado en el Paso 3.
                </span>
            </span>
        </label>
        <div class="attendee-selector">
            <div class="attendee-option">
                {{-- Valor '0' para modo determinista (solo datos definidos) --}}
                <input type="radio" name="puede_inventar_datos_medicos" id="inventar_no" value="0"
                       {{ old('puede_inventar_datos_medicos', '0') == '0' ? 'checked' : '' }}>
                <label for="inventar_no">
                    <span class="attendee-icon">🔒</span>
                    <span class="attendee-label">No (recomendado)</span>
                    <span class="attendee-desc">Garantiza la fidelidad del examen</span>
                </label>
            </div>
            <div class="attendee-option">
                {{-- Valor '1' para modo creativo (improvisación médica) --}}
                <input type="radio" name="puede_inventar_datos_medicos" id="inventar_si" value="1"
                       {{ old('puede_inventar_datos_medicos') == '1' ? 'checked' : '' }}>
                <label for="inventar_si">
                    <span class="attendee-icon">🔓</span>
                    <span class="attendee-label">Sí</span>
                    <span class="attendee-desc">La IA completará la historia clínica</span>
                </label>
            </div>
        </div>
    </div>

    {{-- Objetivos: Metadato para el tutor/sistema de evaluación --}}
    <div class="form-group">
        <label for="learning_objectives">Objetivos de Aprendizaje...</label>
        <textarea id="learning_objectives" name="learning_objectives"
                  placeholder="Ej: Detectar signos de simulación...">{{ old('learning_objectives') }}</textarea>
    </div>
</div>