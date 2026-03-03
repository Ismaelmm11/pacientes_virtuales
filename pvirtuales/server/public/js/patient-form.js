/*
|--------------------------------------------------------------------------
| JS - Formulario de Creación de Pacientes
|--------------------------------------------------------------------------
|
| Gestiona la interactividad del formulario:
| - Añadir/eliminar síntomas dinámicamente
| - Actualizar etiquetas de los sliders de verbosidad y conocimiento médico
|
*/

// ==================== SÍNTOMAS DINÁMICOS ====================

/* Contador global para asegurar que cada nuevo síntoma tenga un índice único 
   en el array que se enviará al servidor (ej: symptoms[1], symptoms[2]) */
let symptomCount = 1;

/**
 * Añade una nueva fila de síntoma al formulario de forma dinámica
 */
function addSymptom() {
    const container = document.getElementById('symptomsContainer');
    const newRow = document.createElement('div');
    newRow.className = 'symptom-row';
    
    /* Insertamos el HTML de la nueva fila usando Template Literals.
       Se utiliza el valor actual de symptomCount para los nombres de los inputs. */
    newRow.innerHTML = `
        <div class="form-group" style="margin-bottom: 0;">
            <label style="font-size: 0.85rem;">Síntoma</label>
            <input type="text" name="symptoms[${symptomCount}][name]" placeholder="Ej: Sudoración fría">
        </div>
        <div class="form-group" style="margin-bottom: 0;">
            <label style="font-size: 0.85rem;">Cuándo lo revela</label>
            <select name="symptoms[${symptomCount}][reveal]">
                <option value="espontaneo">Espontáneamente</option>
                <option value="pregunta_directa">Solo si le preguntan directamente</option>
                <option value="pregunta_especifica">Solo con pregunta específica</option>
            </select>
        </div>
        <button type="button" class="btn-remove-symptom" onclick="removeSymptom(this)">✕</button>
    `;
    
    container.appendChild(newRow); // Agregamos la fila al final del contenedor
    symptomCount++; // Incrementamos el contador para el próximo síntoma
}

/**
 * Elimina la fila de síntoma correspondiente al botón pulsado
 * @param {HTMLElement} button - El botón de cierre que fue clickeado
 */
function removeSymptom(button) {
    /* Accede al padre (div.symptom-row) y lo elimina por completo del DOM */
    button.parentElement.remove();
}

// ==================== SLIDERS (RANGES) ====================

/* Mapeo de valores numéricos a etiquetas descriptivas para mejorar la UX */
const verbosityLabels = ['Muy escueto', 'Escueto', 'Medio', 'Detallista', 'Muy detallista'];
const knowledgeLabels = ['Ninguno', 'Mínimo', 'Básico', 'Moderado', 'Profesional'];

/**
 * Listener para el slider de "Verbosidad" (Nivel de detalle al hablar)
 * Se dispara cada vez que el usuario mueve el control deslizante ('input')
 */
document.getElementById('verbosity_level').addEventListener('input', function () {
    /* Restamos 1 al valor del slider porque los arrays en JS empiezan en 0 */
    document.getElementById('verbosityValue').textContent = verbosityLabels[this.value - 1];
});

/**
 * Listener para el slider de "Conocimiento Médico"
 */
document.getElementById('medical_knowledge').addEventListener('input', function () {
    document.getElementById('knowledgeValue').textContent = knowledgeLabels[this.value - 1];
});

/* INICIALIZACIÓN: 
   Al cargar la página, forzamos la actualización de los textos según el valor 
   que traiga el slider por defecto (útil si hay valores pre-cargados o recargas).
*/
document.getElementById('verbosityValue').textContent = verbosityLabels[document.getElementById('verbosity_level').value - 1];
document.getElementById('knowledgeValue').textContent = knowledgeLabels[document.getElementById('medical_knowledge').value - 1];