{{-- 
|--------------------------------------------------------------------------
| Paso 5: Lógica de Conversación (Control del Escenario)
|--------------------------------------------------------------------------
|
| Aquí se definen las sutilezas que separan a un novato de un experto: 
| la capacidad de detectar mentiras, inconsistencias y manejar crisis.
|
--}}
<div class="section">
    <h2 class="section-title">
        {{-- Icono SVG: Línea de pulso/ECG (Representa la vitalidad del diálogo) --}}
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/>
        </svg>
        Lógica de Conversación
    </h2>
    <p class="section-description">Define las reglas avanzadas que controlan cómo se desarrolla la simulación.</p>

    {{-- 
        GATILLOS EMOCIONALES: 
        Permite entrenar la "noticia difícil" o temas sensibles. 
        Inyecta en el prompt: "Si se menciona X, cambia tu estado emocional a Y".
    --}}
    <div class="form-group">
        <label>Gatillos Emocionales</label>
        <div class="dynamic-list" id="triggersContainer">
            {{-- Campos dinámicos: [Tema] -> [Reacción] --}}
        </div>
        <button type="button" class="btn-add-item" onclick="addTrigger()">
            Añadir Gatillo Emocional
        </button>
    </div>

    {{-- 
        CONTRADICCIONES INTENCIONALES: 
        Esta es una joya pedagógica. Entrena la observación clínica no verbal y 
        la coherencia del relato. Si el alumno no "pilla" la contradicción, 
        el diagnóstico fallará.
    --}}
    <div class="form-group">
        <label>Contradicciones Intencionales</label>
        {{-- Caja informativa para el autor del caso --}}
        <div class="context-box">
            <strong>💡 Consejo pedagógico</strong>
            Entrena la detección de incongruencias entre el relato y la conducta.
        </div>
        <div class="dynamic-list" id="contradictionsContainer">
            {{-- Campos: [Lo que dice] | [La realidad que se observa] | [Respuesta si es descubierto] --}}
        </div>
        <button type="button" class="btn-add-item" onclick="addContradiction()">
            Añadir Contradicción
        </button>
    </div>

    {{-- 
        EVENTOS DE CIERRE: 
        Asegura que la simulación no termine de forma abrupta. 
        Obliga al estudiante a realizar el "Cierre de la consulta" y el plan de seguimiento.
    --}}
    <div class="form-group">
        <label>Eventos de Cierre</label>
        <div class="dynamic-list" id="closureContainer">
            {{-- Campos: [Condición de tiempo/clima] -> [Acción final del paciente] --}}
        </div>
        <button type="button" class="btn-add-item" onclick="addClosure()">
            Añadir Evento de Cierre
        </button>
    </div>

    {{-- 
        INSTRUCCIONES ESPECIALES (Prompt Engineering Final): 
        Aquí es donde el docente puede forzar el uso de lenguaje no verbal 
        usando corchetes [ ] o definir el nivel de hostilidad final.
    --}}
    <div class="form-group">
        <label for="special_instructions">Instrucciones Especiales para la IA</label>
        <textarea id="special_instructions" name="special_instructions" style="min-height: 120px;"
                  placeholder="Ej: Describe acciones no verbales entre corchetes: [Se cruza de brazos y evita la mirada]...">{{ old('special_instructions') }}</textarea>
    </div>
</div>