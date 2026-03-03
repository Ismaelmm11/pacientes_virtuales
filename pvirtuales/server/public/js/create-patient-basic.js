/*
|--------------------------------------------------------------------------
| create-patient-basic.js
|--------------------------------------------------------------------------
|
| Gestiona la interactividad del formulario de creación básico:
|   - Tooltips de ayuda (?)
|   - Selector de acompañante
|   - Grid de personalidad con preview
|   - Sliders con preview de texto
|   - Listas dinámicas (síntomas, medicación, vicios, frases límite)
|
| CLASES CSS ACTUALIZADAS:
|   .section         → .cp-section
|   .form-group      → .cp-form-group
|   .dynamic-list    → .cp-dynamic-list
|   .dynamic-item    → .cp-dynamic-item
|   .item-fields     → .cp-dynamic-item-fields
|   .lie-field       → .cp-lie-field
|   .btn-remove-item → .cp-btn-remove
|   .personality-*   → .cp-personality-*
|   .slider-*        → .cp-slider-*
|   .personality-preview → .cp-preview-box
|
*/

// ==========================================================================
// TOOLTIPS DE AYUDA
// ==========================================================================

document.addEventListener('click', function (e) {
    const icon = e.target.closest('.help-tooltip-icon');
    if (icon) {
        const tooltip = icon.closest('.help-tooltip');
        const wasActive = tooltip.classList.contains('active');

        // Cerrar todos
        document.querySelectorAll('.help-tooltip.active').forEach(t => t.classList.remove('active'));

        if (!wasActive) {
            tooltip.classList.add('active');

            // Comprobar si el bocadillo queda cortado arriba
            const bubble = tooltip.querySelector('.help-tooltip-bubble');
            const rect = bubble.getBoundingClientRect();
            if (rect.top < 10) {
                bubble.classList.add('below');
            } else {
                bubble.classList.remove('below');
            }
        }
        return;
    }

    if (!e.target.closest('.help-tooltip')) {
        document.querySelectorAll('.help-tooltip.active').forEach(t => t.classList.remove('active'));
    }
});

// ==========================================================================
// ACOMPAÑANTE
// ==========================================================================

const attendeeRadios  = document.querySelectorAll('input[name="attendee_type"]');
const companionFields = document.getElementById('companionFields');

if (attendeeRadios.length) {
    attendeeRadios.forEach(radio => {
        radio.addEventListener('change', function () {
            companionFields.classList.toggle('visible', this.value === 'companion');
        });
    });

    const checked = document.querySelector('input[name="attendee_type"]:checked');
    if (checked && checked.value === 'companion') {
        companionFields.classList.add('visible');
    }
}

// ==========================================================================
// PERSONALIDAD CON PREVIEW
// ==========================================================================

const personalityTexts = {
    colaborador:   'Estás tranquilo/a y dispuesto/a a colaborar con el médico. Confías en el sistema sanitario y vienes con buena disposición. Respondes a las preguntas de forma abierta y honesta.',
    ansioso:       'Estás visiblemente nervioso/a y preocupado/a. La incertidumbre sobre tu salud te genera mucha ansiedad. Tiendes a hacer muchas preguntas y a pedir confirmación constante.',
    reservado:     'Eres reservado/a y te cuesta abrirte al médico. Das respuestas cortas y hay que insistir para que des detalles.',
    demandante:    'Estás impaciente y esperas respuestas inmediatas. Interrumpes con frecuencia y cuestionas las decisiones del médico.',
    minimizador:   'Le quitas importancia a tus síntomas. Dices cosas como "seguro que no es nada" o "no sé ni por qué he venido".',
    hipocondriaco: 'Estás muy asustado/a y convencido/a de que tienes algo grave. Has buscado tus síntomas en internet y estás convencido/a del peor escenario.',
    agresivo:      'Estás enfadado/a y a la defensiva. Respondes de forma cortante y puedes llegar a levantar la voz si te sientes cuestionado/a.',
    deprimido:     'Estás apático/a y sin energía. Hablas en voz baja y con desgana. Te cuesta expresar lo que sientes porque "da igual".',
    desconfiado:   'No confías en los médicos ni en el sistema sanitario. Cuestionas todo lo que te dicen y pides segundas opiniones.',
    confuso:       'Estás desorientado/a y te cuesta seguir la conversación. Puedes contradecirte sobre fechas o detalles.',
    evasivo:       'Evitas responder a ciertas preguntas o cambias de tema cuando la conversación se acerca a algo incómodo.',
};

const personalityRadios      = document.querySelectorAll('input[name="personality_type"]');
const personalityPreview     = document.getElementById('personalityPreview');
const personalityPreviewText = document.getElementById('personalityPreviewText');
const customToggle           = document.getElementById('personalityCustomToggle');
const customField            = document.getElementById('personalityCustomField');
const customTextarea         = document.getElementById('personalityCustomText');

if (personalityRadios.length) {
    personalityRadios.forEach(radio => {
        radio.addEventListener('change', function () {
            const text = personalityTexts[this.value];
            if (text && personalityPreview) {
                personalityPreviewText.textContent = text;
                personalityPreview.classList.add('visible');
                if (customField && customField.style.display === 'block') {
                    customTextarea.value = text;
                }
            }
        });
    });

    const checkedP = document.querySelector('input[name="personality_type"]:checked');
    if (checkedP && personalityTexts[checkedP.value]) {
        personalityPreviewText.textContent = personalityTexts[checkedP.value];
        personalityPreview.classList.add('visible');
    }
}

if (customToggle) {
    customToggle.addEventListener('click', function () {
        const isOpen = customField.style.display === 'block';
        customField.style.display = isOpen ? 'none' : 'block';
        customToggle.textContent = isOpen ? '✏️ Quiero personalizar este texto' : '✕ Usar texto automático';
        if (!isOpen) {
            const checked = document.querySelector('input[name="personality_type"]:checked');
            if (checked && personalityTexts[checked.value]) {
                customTextarea.value = personalityTexts[checked.value];
            }
        }
    });
}

// ==========================================================================
// SLIDERS CON PREVIEW
// ==========================================================================

const verbosityDescriptions = {
    1: 'Muy escueto: respuestas de pocas palabras. Ejemplo: "Sí." "No." "Aquí." "Desde ayer."',
    2: 'Escueto: respuestas cortas y directas. Ejemplo: "Me duele la espalda desde el lunes."',
    3: 'Normal: respuestas de longitud media. Ejemplo: "Me duele la espalda desde el lunes, sobre todo cuando me agacho."',
    4: 'Detallista: da bastante contexto sin que se lo pidan. Ejemplo: "Pues mire, el lunes estaba en casa recogiendo la compra y al agacharme noté un dolor fuerte..."',
    5: 'Muy detallista: tiende a extenderse y divagar. Ejemplo: "Ay, pues verá, el lunes estaba yo en el Mercadona, que ahora está carísimo todo, y resulta que compré muchas cosas porque venía mi hija..."',
};

const knowledgeDescriptions = {
    1: 'Ningún conocimiento médico. Usa términos muy coloquiales: "me duele aquí", "tengo la tripa revuelta", "se me duerme el brazo".',
    2: 'Conocimiento mínimo. Sabe lo básico: "creo que es la tensión", "me dijeron algo de azúcar en la sangre".',
    3: 'Conocimiento básico. Entiende términos comunes: "tengo hipertensión", "me recetaron antiinflamatorios".',
    4: 'Conocimiento moderado. Ha leído sobre su condición: "creo que podría ser una ciática", "tenía el colesterol LDL alto".',
    5: 'Profesional sanitario. Usa terminología técnica: "dolor precordial opresivo con irradiación a MSI", "sospecho un SCA".',
};

const verbosityLabels = ['Muy escueto', 'Escueto', 'Normal', 'Detallista', 'Muy detallista'];
const knowledgeLabels = ['Ninguno', 'Mínimo', 'Básico', 'Moderado', 'Profesional'];

function initSlider(sliderId, valueId, previewId, labels, descriptions) {
    const slider  = document.getElementById(sliderId);
    const valueEl = document.getElementById(valueId);
    const preview = document.getElementById(previewId);
    if (!slider) return;

    function update() {
        const val = parseInt(slider.value);
        if (valueEl) valueEl.textContent = labels[val - 1];
        if (preview) {
            preview.innerHTML = `<div class="cp-preview-label">Texto que se generará en el prompt:</div>${descriptions[val]}`;
        }
    }

    slider.addEventListener('input', update);
    update();
}

initSlider('verbosity_level', 'verbosityValue', 'verbosityPreview', verbosityLabels, verbosityDescriptions);
initSlider('medical_knowledge', 'knowledgeValue', 'knowledgePreview', knowledgeLabels, knowledgeDescriptions);

// Toggles de personalización de sliders
function initCustomToggle(toggleId, fieldId, textareaId, descObj, sliderId) {
    const toggle   = document.getElementById(toggleId);
    const field    = document.getElementById(fieldId);
    const textarea = document.getElementById(textareaId);
    const slider   = document.getElementById(sliderId);
    if (!toggle) return;

    toggle.addEventListener('click', function () {
        const isOpen = field.style.display === 'block';
        field.style.display = isOpen ? 'none' : 'block';
        toggle.textContent = isOpen ? '✏️ Quiero personalizar este texto' : '✕ Usar texto automático';
        if (!isOpen && slider) textarea.value = descObj[parseInt(slider.value)];
    });

    if (slider) {
        slider.addEventListener('input', function () {
            if (field.style.display === 'block') textarea.value = descObj[parseInt(this.value)];
        });
    }
}

initCustomToggle('verbosityCustomToggle', 'verbosityCustomField', 'verbosityCustomText', verbosityDescriptions, 'verbosity_level');
initCustomToggle('knowledgeCustomToggle', 'knowledgeCustomField', 'knowledgeCustomText', knowledgeDescriptions, 'medical_knowledge');

// ==========================================================================
// UTILIDADES DE LISTAS DINÁMICAS
// ==========================================================================

function removeItem(button) {
    const container = button.closest('.cp-dynamic-list');
    button.closest('.cp-dynamic-item').remove();
    updateRemoveButtons(container);
}

function updateRemoveButtons(container) {
    if (!container) return;
    const items = container.querySelectorAll('.cp-dynamic-item');
    items.forEach(item => {
        const btn = item.querySelector('.cp-btn-remove');
        if (btn) btn.style.visibility = items.length <= 1 ? 'hidden' : 'visible';
    });
}

function handleRevealChange(select) {
    const item = select.closest('.cp-dynamic-item');
    const lieField = item ? item.querySelector('.cp-lie-field') : null;
    if (lieField) lieField.classList.toggle('visible', select.value === 'miente');
}

// ==========================================================================
// SÍNTOMAS
// ==========================================================================

let symptomCount = document.querySelectorAll('#symptomsContainer .cp-dynamic-item').length || 1;

function addSymptom() {
    const container = document.getElementById('symptomsContainer');
    const item = document.createElement('div');
    item.className = 'cp-dynamic-item';
    item.innerHTML = `
        <div class="cp-dynamic-item-fields">
            <div class="cp-dynamic-item-field">
                <label>Síntoma</label>
                <input type="text" name="symptoms[${symptomCount}][name]" placeholder="Ej: Sudoración fría">
            </div>
            <div class="cp-dynamic-item-field">
                <label>Cuándo lo revela</label>
                <select name="symptoms[${symptomCount}][reveal]" class="reveal-select" onchange="handleRevealChange(this)">
                    <option value="espontaneo">Espontáneamente</option>
                    <option value="pregunta">Si le preguntan</option>
                    <option value="oculta">Lo oculta</option>
                    <option value="miente">Miente</option>
                    <option value="exagera">Exagera</option>
                </select>
            </div>
            <div class="cp-lie-field" id="lie_symptoms_${symptomCount}">
                <label>¿Qué dice en su lugar?</label>
                <input type="text" name="symptoms[${symptomCount}][lie_text]" placeholder="Vacío = la IA improvisa">
                <p class="cp-lie-hint">Si se deja vacío, la IA inventará la mentira de forma coherente.</p>
            </div>
        </div>
        <button type="button" class="cp-btn-remove" onclick="removeItem(this)">
            <i data-lucide="x"></i>
        </button>
    `;
    container.appendChild(item);
    updateRemoveButtons(container);
    lucide.createIcons(); // Renderizar el icono X recién añadido
    symptomCount++;
}

// ==========================================================================
// MEDICACIÓN
// ==========================================================================

let medicationCount = 0;

function addMedication() {
    const container = document.getElementById('medicationsContainer');
    const item = document.createElement('div');
    item.className = 'cp-dynamic-item';
    item.innerHTML = `
        <div class="cp-dynamic-item-fields">
            <div class="cp-dynamic-item-field">
                <label>Medicamento</label>
                <input type="text" name="medications[${medicationCount}][name]" placeholder="Ej: Enalapril 10mg">
            </div>
            <div class="cp-dynamic-item-field">
                <label>Cuándo lo toma</label>
                <input type="text" name="medications[${medicationCount}][frequency]" placeholder="Ej: Una vez al día / Cuando me duele la cabeza">
            </div>
        </div>
        <button type="button" class="cp-btn-remove" onclick="removeItem(this)">
            <i data-lucide="x"></i>
        </button>
    `;
    container.appendChild(item);
    updateRemoveButtons(container);
    lucide.createIcons();
    medicationCount++;
}

// ==========================================================================
// VICIOS
// ==========================================================================

let viceCount = 0;

function addVice() {
    const container = document.getElementById('vicesContainer');
    const item = document.createElement('div');
    item.className = 'cp-dynamic-item';
    item.innerHTML = `
        <div class="cp-dynamic-item-fields">
            <div class="cp-dynamic-item-field">
                <label>Vicio</label>
                <input type="text" name="vices[${viceCount}][name]" placeholder="Ej: Tabaco: 2 paquetes al día">
            </div>
            <div class="cp-dynamic-item-field">
                <label>Cuándo lo revela</label>
                <select name="vices[${viceCount}][reveal]" class="reveal-select" onchange="handleRevealChange(this)">
                    <option value="espontaneo">Espontáneamente</option>
                    <option value="pregunta">Si le preguntan</option>
                    <option value="oculta">Lo oculta</option>
                    <option value="miente">Miente</option>
                </select>
            </div>
            <div class="cp-lie-field">
                <label>¿Qué dice en su lugar?</label>
                <input type="text" name="vices[${viceCount}][lie_text]" placeholder="Vacío = la IA improvisa">
                <p class="cp-lie-hint">Si se deja vacío, la IA inventará la mentira de forma coherente.</p>
            </div>
        </div>
        <button type="button" class="cp-btn-remove" onclick="removeItem(this)">
            <i data-lucide="x"></i>
        </button>
    `;
    container.appendChild(item);
    updateRemoveButtons(container);
    lucide.createIcons();
    viceCount++;
}

// ==========================================================================
// FRASES LÍMITE
// ==========================================================================

let fraseLimiteCount = 0;

function addFraseLimite() {
    const container = document.getElementById('frasesLimiteContainer');
    const item = document.createElement('div');
    item.className = 'cp-dynamic-item';
    item.innerHTML = `
        <div class="cp-dynamic-item-fields" style="grid-template-columns: 1fr;">
            <div class="cp-dynamic-item-field">
                <label>Frase</label>
                <input type="text" name="frases_limite[${fraseLimiteCount}]"
                       placeholder="Ej: No lo sé, eso lo tenéis vosotros en el ordenador">
            </div>
        </div>
        <button type="button" class="cp-btn-remove" onclick="removeItem(this)">
            <i data-lucide="x"></i>
        </button>
    `;
    container.appendChild(item);
    updateRemoveButtons(container);
    lucide.createIcons();
    fraseLimiteCount++;
}

// ==========================================================================
// INICIALIZACIÓN
// ==========================================================================

document.addEventListener('DOMContentLoaded', function () {
    // Scroll a errores de validación si los hay
    const errors = document.getElementById('validationErrors');
    if (errors) errors.scrollIntoView({ behavior: 'smooth', block: 'center' });

    // Inicializar botones de eliminar en el primer síntoma
    const symptomsContainer = document.getElementById('symptomsContainer');
    if (symptomsContainer) updateRemoveButtons(symptomsContainer);
});