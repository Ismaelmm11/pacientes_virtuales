/*
|--------------------------------------------------------------------------
| JS - Formulario de Creación de Pacientes (Avanzado) — Wizard
|--------------------------------------------------------------------------
|
| Gestiona:
| - Wizard: navegación entre pasos, barra de progreso, validación por paso
| - Todo lo del básico (tooltips, acompañante, personalidad, sliders)
| - Listas dinámicas ampliadas: antecedentes, medicación, vicios,
|   contradicciones, reglas de interacción, gatillos, eventos de cierre
|
*/

// ==================== SCROLL A ERRORES DE VALIDACIÓN ====================

document.addEventListener('DOMContentLoaded', function () {
    const alertDanger = document.querySelector('.alert-danger');
    if (alertDanger) {
        alertDanger.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }
});

// ==================== TOOLTIPS DE AYUDA ====================
// (Idéntico al básico)

document.addEventListener('click', function (e) {
    const icon = e.target.closest('.help-tooltip-icon');
    if (icon) {
        const tooltip = icon.closest('.help-tooltip');
        const wasActive = tooltip.classList.contains('active');
        document.querySelectorAll('.help-tooltip.active').forEach(t => t.classList.remove('active'));
        if (!wasActive) {
            tooltip.classList.add('active');
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

// ==================== WIZARD ====================

let currentStep = 0;
const panels = document.querySelectorAll('.wizard-panel');
const indicators = document.querySelectorAll('.wizard-step-indicator');
const totalSteps = panels.length;

function goToStep(step) {
    if (step < 0 || step >= totalSteps) return;

    // Ocultar panel actual
    panels[currentStep].classList.remove('active');
    indicators[currentStep].classList.remove('active');

    // Marcar como completado si avanzamos
    if (step > currentStep) {
        indicators[currentStep].classList.add('completed');
    }

    // Mostrar nuevo panel
    currentStep = step;
    panels[currentStep].classList.add('active');
    indicators[currentStep].classList.add('active');

    // Scroll al top del formulario
    document.querySelector('.form-body').scrollIntoView({ behavior: 'smooth', block: 'start' });

    // Actualizar botones de navegación
    updateWizardNav();
}

function nextStep() {
    goToStep(currentStep + 1);
}

function prevStep() {
    // Quitar completado al volver
    indicators[currentStep].classList.remove('completed');
    goToStep(currentStep - 1);
}

function updateWizardNav() {
    // Cada panel tiene sus propios botones, así que los actualizamos
    panels.forEach((panel, i) => {
        const prevBtn = panel.querySelector('.btn-wizard-prev');
        const nextBtn = panel.querySelector('.btn-wizard-next');
        const submitBtn = panel.querySelector('.btn-submit');
        const info = panel.querySelector('.wizard-nav-info');

        if (prevBtn) prevBtn.style.display = i === 0 ? 'none' : 'inline-flex';
        if (nextBtn) nextBtn.style.display = i === totalSteps - 1 ? 'none' : 'inline-flex';
        if (submitBtn) submitBtn.style.display = i === totalSteps - 1 ? 'inline-flex' : 'none';
        if (info) info.textContent = `Paso ${i + 1} de ${totalSteps}`;
    });
}

// Click en indicadores del wizard
indicators.forEach((ind, i) => {
    ind.addEventListener('click', () => {
        // Marcar todos los pasos entre el actual y el destino como completados
        if (i > currentStep) {
            for (let j = currentStep; j < i; j++) {
                indicators[j].classList.add('completed');
            }
        }
        goToStep(i);
    });
});

// Inicializar
if (panels.length) {
    updateWizardNav();
}

// ==================== ACOMPAÑANTE ====================

const attendeeRadios = document.querySelectorAll('input[name="attendee_type"]');
const companionFields = document.getElementById('companionFields');

if (attendeeRadios.length) {
    attendeeRadios.forEach(radio => {
        radio.addEventListener('change', function () {
            if (this.value === 'companion') {
                companionFields.classList.add('visible');
            } else {
                companionFields.classList.remove('visible');
            }
        });
    });
    const checkedAttendee = document.querySelector('input[name="attendee_type"]:checked');
    if (checkedAttendee && checkedAttendee.value === 'companion') {
        companionFields.classList.add('visible');
    }
}

// ==================== PERSONALIDAD CON PREVIEW ====================

const personalityTexts = {
    colaborador: { text: 'Estás tranquilo/a y dispuesto/a a colaborar con el médico. Confías en el sistema sanitario y vienes con buena disposición. Respondes a las preguntas de forma abierta y honesta.' },
    ansioso: { text: 'Estás visiblemente nervioso/a y preocupado/a. La incertidumbre sobre tu salud te genera mucha ansiedad. Tiendes a hacer muchas preguntas y a pedir confirmación constante de que no es nada grave.' },
    reservado: { text: 'Eres reservado/a y te cuesta abrirte al médico. No te sientes cómodo/a hablando de temas personales con desconocidos. Das respuestas cortas y hay que insistir para que des detalles.' },
    demandante: { text: 'Estás impaciente y esperas respuestas inmediatas. Sientes que llevas demasiado tiempo con este problema sin solución. Interrumpes con frecuencia y cuestionas las decisiones del médico.' },
    minimizador: { text: 'Le quitas importancia a tus síntomas. No quieres parecer exagerado/a ni hacer perder el tiempo al médico. Dices cosas como "seguro que no es nada" o "no sé ni por qué he venido".' },
    hipocondriaco: { text: 'Estás muy asustado/a y convencido/a de que tienes algo grave. Cada síntoma te parece señal de una enfermedad seria. Has buscado tus síntomas en internet y estás convencido/a del peor escenario.' },
    agresivo: { text: 'Estás enfadado/a y a la defensiva. Sientes que nadie te toma en serio o que el sistema sanitario te ha fallado. Respondes de forma cortante y puedes llegar a levantar la voz si te sientes cuestionado/a.' },
    deprimido: { text: 'Estás apático/a y sin energía. Hablas en voz baja y con desgana. Te cuesta expresar lo que sientes porque "da igual". Puedes mostrar desinterés por el tratamiento o la recuperación.' },
    desconfiado: { text: 'No confías en los médicos ni en el sistema sanitario. Cuestionas todo lo que te dicen y pides segundas opiniones. Puedes mencionar remedios caseros o tratamientos alternativos como mejor opción.' },
    confuso: { text: 'Estás desorientado/a y te cuesta seguir la conversación. Puedes contradecirte sobre fechas o detalles. Necesitas que te repitan las cosas y a veces respondes a una pregunta diferente de la que te han hecho.' },
    evasivo: { text: 'Evitas responder a ciertas preguntas o cambias de tema cuando la conversación se acerca a algo incómodo. Das rodeos y divagaciones en vez de respuestas directas. Hay algo que no quieres contar.' }
};

const personalityRadios = document.querySelectorAll('input[name="personality_type"]');
const personalityPreview = document.getElementById('personalityPreview');
const personalityPreviewText = document.getElementById('personalityPreviewText');
const customToggle = document.getElementById('personalityCustomToggle');
const customField = document.getElementById('personalityCustomField');
const customTextarea = document.getElementById('personalityCustomText');

if (personalityRadios.length) {
    personalityRadios.forEach(radio => {
        radio.addEventListener('change', function () {
            const data = personalityTexts[this.value];
            if (data && personalityPreview) {
                personalityPreviewText.textContent = data.text;
                personalityPreview.classList.add('visible');
                if (customField && customField.style.display !== 'none') {
                    customTextarea.value = data.text;
                }
            }
        });
    });
    const checkedPersonality = document.querySelector('input[name="personality_type"]:checked');
    if (checkedPersonality && personalityTexts[checkedPersonality.value]) {
        personalityPreviewText.textContent = personalityTexts[checkedPersonality.value].text;
        personalityPreview.classList.add('visible');
    }
}

if (customToggle) {
    customToggle.addEventListener('click', function () {
        if (customField.style.display === 'block') {
            customField.style.display = 'none';
            customToggle.textContent = '✏️ Quiero personalizar este texto';
        } else {
            customField.style.display = 'block';
            customToggle.textContent = '✕ Usar texto automático';
            const checked = document.querySelector('input[name="personality_type"]:checked');
            if (checked && personalityTexts[checked.value]) {
                customTextarea.value = personalityTexts[checked.value].text;
            }
        }
    });
}

// ==================== SLIDERS CON PREVIEW ====================

const verbosityDescriptions = {
    1: 'Muy escueto: respuestas de pocas palabras. Ejemplo: "Sí." "No." "Aquí." "Desde ayer."',
    2: 'Escueto: respuestas cortas y directas. Ejemplo: "Me duele la espalda desde el lunes."',
    3: 'Normal: respuestas de longitud media. Ejemplo: "Me duele la espalda desde el lunes, sobre todo cuando me agacho."',
    4: 'Detallista: da bastante contexto sin que se lo pidan. Ejemplo: "Pues mire, el lunes estaba en casa recogiendo la compra y al agacharme noté un dolor fuerte..."',
    5: 'Muy detallista: tiende a extenderse y divagar. Ejemplo: "Ay, pues verá, el lunes estaba yo en el Mercadona, que ahora está carísimo todo..."'
};

const knowledgeDescriptions = {
    1: 'Ningún conocimiento médico. Usa términos muy coloquiales: "me duele aquí", "tengo la tripa revuelta".',
    2: 'Conocimiento mínimo. Sabe lo básico: "creo que es la tensión", "me dijeron algo de azúcar en la sangre".',
    3: 'Conocimiento básico. Términos comunes: "tengo hipertensión", "me recetaron antiinflamatorios".',
    4: 'Conocimiento moderado. Ha leído sobre su condición: "creo que podría ser una ciática".',
    5: 'Profesional sanitario. Terminología técnica: "dolor precordial opresivo con irradiación a MSI".'
};

const verbosityLabels = ['Muy escueto', 'Escueto', 'Normal', 'Detallista', 'Muy detallista'];
const knowledgeLabels = ['Ninguno', 'Mínimo', 'Básico', 'Moderado', 'Profesional'];

function initSlider(sliderId, valueId, previewId, labels, descriptions) {
    const slider = document.getElementById(sliderId);
    const valueSpan = document.getElementById(valueId);
    const preview = document.getElementById(previewId);
    if (!slider) return;
    function update() {
        const val = parseInt(slider.value);
        valueSpan.textContent = labels[val - 1];
        if (preview) {
            preview.innerHTML = '<div class="preview-label">Texto que se generará en el prompt:</div>' + descriptions[val];
        }
    }
    slider.addEventListener('input', update);
    update();
}

initSlider('verbosity_level', 'verbosityValue', 'verbosityPreview', verbosityLabels, verbosityDescriptions);
initSlider('medical_knowledge', 'knowledgeValue', 'knowledgePreview', knowledgeLabels, knowledgeDescriptions);

// ==================== FUNCIÓN GENÉRICA: REVELACIÓN ====================

function handleRevealChange(select) {
    const item = select.closest('.dynamic-item');
    const lieField = item ? item.querySelector('.lie-field') : null;
    if (lieField) {
        lieField.classList.toggle('visible', select.value === 'miente');
    }
}

// ==================== FUNCIÓN GENÉRICA: ELIMINAR ITEM ====================

function removeItem(button) {
    const container = button.closest('.dynamic-list');
    button.closest('.dynamic-item').remove();
    updateRemoveButtons(container);
}

// Muestra/oculta botones ✕ según si hay más de 1 item en el contenedor
function updateRemoveButtons(container) {
    if (!container) return;
    const items = container.querySelectorAll('.dynamic-item');
    items.forEach(item => {
        const btn = item.querySelector('.btn-remove-item');
        if (btn) btn.style.visibility = items.length <= 1 ? 'hidden' : 'visible';
    });
}

// ==================== FUNCIÓN GENÉRICA: ADHERENCIA ====================

function handleAdherenceChange(checkbox) {
    const detail = checkbox.closest('.dynamic-item').querySelector('.adherence-detail');
    if (detail) {
        detail.classList.toggle('visible', !checkbox.checked);
    }
}

// ==================== GENERADOR DE TOOLTIP ====================

function tooltipHTML(title, description, examples) {
    let html = `<span class="help-tooltip">
        <span class="help-tooltip-icon">?</span>
        <span class="help-tooltip-bubble">
            <strong>${title}</strong>
            ${description}`;
    if (examples && examples.length) {
        examples.forEach(ex => {
            html += `<div class="example">📝 ${ex}</div>`;
        });
    }
    html += `</span></span>`;
    return html;
}

// ==================== GENERADOR DE REVELACIÓN SELECT ====================

function revealSelectHTML(prefix, index) {
    return `
        <select name="${prefix}[${index}][reveal]" class="reveal-select" onchange="handleRevealChange(this)">
            <option value="espontaneo">Espontáneamente</option>
            <option value="pregunta_directa">Solo si le preguntan directamente</option>
            <option value="pregunta_relacionada">Si preguntan algo relacionado</option>
            <option value="oculta">Lo oculta (no lo admitirá)</option>
            <option value="miente">Miente sobre esto</option>
        </select>`;
}

function lieFieldHTML(prefix, index) {
    return `
        <div class="lie-field">
            <label>¿Qué dice en su lugar? ${tooltipHTML('¿Para qué sirve?', 'La mentira concreta. Si lo dejas vacío, la IA inventará una coherente.', ['"Dice que no le duele nada"'])}</label>
            <input type="text" name="${prefix}[${index}][lie_text]" placeholder="Vacío = la IA improvisa la mentira">
            <p class="lie-hint">Si se deja vacío, la IA inventará la mentira de forma coherente.</p>
        </div>`;
}

// ==================== SÍNTOMAS ====================

let symptomCount = document.querySelectorAll('#symptomsContainer .dynamic-item').length || 1;

function addSymptom() {
    const container = document.getElementById('symptomsContainer');
    const i = symptomCount++;
    const item = document.createElement('div');
    item.className = 'dynamic-item';
    item.innerHTML = `
        <div class="item-fields symptom-fields-advanced">
            <div class="item-field">
                <label>Síntoma ${tooltipHTML('¿Para qué sirve?', 'Describe el síntoma tal como lo expresaría el paciente.', ['"Sudoración fría"', '"Se me duerme el brazo"'])}</label>
                <input type="text" name="symptoms[${i}][name]" placeholder="Ej: Sudoración fría">
            </div>
            <div class="item-field">
                <label>Intensidad (1-10) ${tooltipHTML('¿Para qué sirve?', 'Escala del dolor cuando el estudiante pregunte "del 1 al 10".', ['3 = leve, 7 = importante, 10 = insoportable'])}</label>
                <input type="number" name="symptoms[${i}][intensity]" min="1" max="10" placeholder="Ej: 7">
            </div>
            <div class="item-field">
                <label>Agravantes ${tooltipHTML('¿Para qué sirve?', 'Qué empeora el síntoma. Clave para diagnóstico diferencial.', ['"Esfuerzo físico, estrés"'])}</label>
                <input type="text" name="symptoms[${i}][aggravating]" placeholder="Ej: Al hacer esfuerzo">
            </div>
            <div class="item-field">
                <label>Atenuantes ${tooltipHTML('¿Para qué sirve?', 'Qué mejora el síntoma. Ayuda a orientar el diagnóstico.', ['"Reposo, sentarse"'])}</label>
                <input type="text" name="symptoms[${i}][relieving]" placeholder="Ej: En reposo">
            </div>
            <div class="item-field">
                <label>Revelación ${tooltipHTML('¿Para qué sirve?', 'Controla CUÁNDO el paciente comparte este síntoma.', ['"Espontáneamente" → Lo dice sin que le pregunten', '"Miente" → Dice otra cosa'])}</label>
                ${revealSelectHTML('symptoms', i)}
            </div>
            ${lieFieldHTML('symptoms', i)}
        </div>
        <button type="button" class="btn-remove-item" onclick="removeItem(this)">✕</button>`;
    container.appendChild(item);
    updateRemoveButtons(container);
}

// ==================== ANTECEDENTES (Enfermedades) ====================

let diseaseCount = document.querySelectorAll('#diseasesContainer .dynamic-item').length || 0;

function addDisease() {
    const container = document.getElementById('diseasesContainer');
    const i = diseaseCount++;
    const item = document.createElement('div');
    item.className = 'dynamic-item antecedent-item';
    item.innerHTML = `
        <div class="item-fields">
            <div class="item-field">
                <label>Enfermedad ${tooltipHTML('¿Para qué sirve?', 'Nombre de la enfermedad previa del paciente.', ['"Hipertensión arterial"', '"Diabetes tipo 2"'])}</label>
                <input type="text" name="diseases[${i}][name]" placeholder="Ej: Hipertensión arterial">
            </div>
            <div class="item-field">
                <label>Hace cuánto ${tooltipHTML('¿Para qué sirve?', 'Cuánto tiempo hace que fue diagnosticada.', ['"5 años"', '"Desde la infancia"'])}</label>
                <input type="text" name="diseases[${i}][since]" placeholder="Ej: 5 años">
            </div>
            <div class="item-field">
                <label>Revelación ${tooltipHTML('¿Para qué sirve?', 'Cuándo comparte esta información con el médico.', ['"Solo si le preguntan" → Hay que saber preguntar'])}</label>
                ${revealSelectHTML('diseases', i)}
            </div>
            ${lieFieldHTML('diseases', i)}
        </div>
        <button type="button" class="btn-remove-item" onclick="removeItem(this)">✕</button>`;
    container.appendChild(item);
    updateRemoveButtons(container);
}

// ==================== ANTECEDENTES (Cirugías) ====================

let surgeryCount = document.querySelectorAll('#surgeriesContainer .dynamic-item').length || 0;

function addSurgery() {
    const container = document.getElementById('surgeriesContainer');
    const i = surgeryCount++;
    const item = document.createElement('div');
    item.className = 'dynamic-item antecedent-item';
    item.innerHTML = `
        <div class="item-fields">
            <div class="item-field">
                <label>Cirugía ${tooltipHTML('¿Para qué sirve?', 'Nombre de la intervención quirúrgica previa.', ['"Apendicectomía"', '"Bypass gástrico"'])}</label>
                <input type="text" name="surgeries[${i}][name]" placeholder="Ej: Apendicectomía">
            </div>
            <div class="item-field">
                <label>Hace cuánto ${tooltipHTML('¿Para qué sirve?', 'Cuánto tiempo hace que se operó.', ['"10 años"', '"El año pasado"'])}</label>
                <input type="text" name="surgeries[${i}][since]" placeholder="Ej: 10 años">
            </div>
            <div class="item-field">
                <label>Revelación ${tooltipHTML('¿Para qué sirve?', 'Cuándo comparte esta información con el médico.', ['"Espontáneamente" → Lo dice sin que le pregunten'])}</label>
                ${revealSelectHTML('surgeries', i)}
            </div>
            ${lieFieldHTML('surgeries', i)}
        </div>
        <button type="button" class="btn-remove-item" onclick="removeItem(this)">✕</button>`;
    container.appendChild(item);
    updateRemoveButtons(container);
}

// ==================== MEDICACIÓN ====================

let medCount = document.querySelectorAll('#medicationsContainer .dynamic-item').length || 0;

function addMedication() {
    const container = document.getElementById('medicationsContainer');
    const i = medCount++;
    const item = document.createElement('div');
    item.className = 'dynamic-item medication-item';
    item.innerHTML = `
        <div class="item-fields">
            <div class="item-field">
                <label>Medicamento ${tooltipHTML('¿Para qué sirve?', 'Nombre del fármaco que toma o debería tomar.', ['"Enalapril"', '"Metformina"'])}</label>
                <input type="text" name="medications[${i}][name]" placeholder="Ej: Enalapril">
            </div>
            <div class="item-field">
                <label>Dosis ${tooltipHTML('¿Para qué sirve?', 'Cantidad que toma por toma.', ['"10mg"', '"500mg"', '"2 comprimidos"'])}</label>
                <input type="text" name="medications[${i}][dose]" placeholder="Ej: 10mg">
            </div>
            <div class="item-fields-row2">
                <div class="item-field">
                    <label>Frecuencia ${tooltipHTML('¿Para qué sirve?', 'Con qué frecuencia toma el medicamento.', ['"1 vez al día"', '"Cada 8 horas"'])}</label>
                    <input type="text" name="medications[${i}][frequency]" placeholder="Ej: 1 vez al día">
                </div>
                <div class="item-field">
                    <div class="adherence-wrapper">
                        <input type="checkbox" name="medications[${i}][adherence]" id="adherence_${i}" value="1" checked onchange="handleAdherenceChange(this)">
                        <label for="adherence_${i}">Se lo toma bien ${tooltipHTML('¿Para qué sirve?', 'Si está desmarcado, el paciente NO cumple bien con este medicamento. Escribe el detalle abajo.', ['"Se lo salta los fines de semana"'])}</label>
                    </div>
                    <div class="adherence-detail">
                        <input type="text" name="medications[${i}][adherence_detail]" placeholder="Ej: Se lo salta los fines de semana">
                    </div>
                </div>
                <div class="item-field">
                    <label>Revelación ${tooltipHTML('¿Para qué sirve?', 'Cuándo comparte esta información con el médico.', ['"Miente" → Dice que se lo toma bien cuando no es así'])}</label>
                    ${revealSelectHTML('medications', i)}
                </div>
            </div>
            ${lieFieldHTML('medications', i)}
        </div>
        <button type="button" class="btn-remove-item" onclick="removeItem(this)">✕</button>`;
    container.appendChild(item);
    updateRemoveButtons(container);
}

// ==================== VICIOS ====================

let viceCount = document.querySelectorAll('#vicesContainer .dynamic-item').length || 0;

function addVice() {
    const container = document.getElementById('vicesContainer');
    const i = viceCount++;
    const item = document.createElement('div');
    item.className = 'dynamic-item vice-item';
    item.innerHTML = `
        <div class="item-fields">
            <div class="item-field">
                <label>Vicio / Hábito ${tooltipHTML('¿Para qué sirve?', 'Tipo de sustancia o hábito nocivo.', ['"Tabaco"', '"Alcohol"', '"Cannabis"'])}</label>
                <input type="text" name="vices[${i}][name]" placeholder="Ej: Tabaco">
            </div>
            <div class="item-field">
                <label>Cantidad / Frecuencia ${tooltipHTML('¿Para qué sirve?', 'Cuánto consume. La IA usará este dato si el estudiante pregunta.', ['"2 paquetes al día"', '"Una botella de vino diaria"'])}</label>
                <input type="text" name="vices[${i}][frequency]" placeholder="Ej: 2 paquetes al día">
            </div>
            <div class="item-field">
                <label>Revelación ${tooltipHTML('¿Para qué sirve?', 'Cuándo lo admite. Los vicios suelen ocultarse o minimizarse.', ['"Miente" → "Lo normal, un par de cervezas"'])}</label>
                ${revealSelectHTML('vices', i)}
            </div>
            ${lieFieldHTML('vices', i)}
        </div>
        <button type="button" class="btn-remove-item" onclick="removeItem(this)">✕</button>`;
    container.appendChild(item);
    updateRemoveButtons(container);
}

// ==================== REGLAS DE INTERACCIÓN ====================

let ruleCount = document.querySelectorAll('#rulesContainer .dynamic-item').length || 0;

function addRule() {
    const container = document.getElementById('rulesContainer');
    const i = ruleCount++;
    const item = document.createElement('div');
    item.className = 'dynamic-item rule-item';
    item.innerHTML = `
        <div class="item-fields">
            <div class="item-field">
                <label>Si el alumno... ${tooltipHTML('¿Para qué sirve?', 'La acción o comportamiento del estudiante que activa esta regla.', ['"Le tutea sin permiso"', '"Le interrumpe varias veces"'])}</label>
                <input type="text" name="rules[${i}][condition]" placeholder="Ej: Le tutea sin permiso">
            </div>
            <div class="item-field">
                <label>Entonces el paciente... ${tooltipHTML('¿Para qué sirve?', 'Cómo reacciona el paciente ante esa acción.', ['"Se muestra incómodo y se cierra"', '"Levanta la voz"'])}</label>
                <input type="text" name="rules[${i}][reaction]" placeholder="Ej: Se muestra incómodo y se cierra">
            </div>
        </div>
        <button type="button" class="btn-remove-item" onclick="removeItem(this)">✕</button>`;
    container.appendChild(item);
    updateRemoveButtons(container);
}

// ==================== GATILLOS EMOCIONALES ====================

let triggerCount = document.querySelectorAll('#triggersContainer .dynamic-item').length || 0;

function addTrigger() {
    const container = document.getElementById('triggersContainer');
    const i = triggerCount++;
    const item = document.createElement('div');
    item.className = 'dynamic-item trigger-item';
    item.innerHTML = `
        <div class="item-fields">
            <div class="item-field">
                <label>Si se menciona... ${tooltipHTML('¿Para qué sirve?', 'El tema sensible que provoca una reacción emocional.', ['"Su padre fallecido"', '"El divorcio"'])}</label>
                <input type="text" name="triggers[${i}][topic]" placeholder="Ej: Su padre fallecido">
            </div>
            <div class="item-field">
                <label>Reacciona... ${tooltipHTML('¿Para qué sirve?', 'Cómo cambia el comportamiento del paciente.', ['"Se pone nervioso, cambia de tema"', '"Se le saltan las lágrimas"'])}</label>
                <input type="text" name="triggers[${i}][reaction]" placeholder="Ej: Se pone nervioso, cambia de tema">
            </div>
        </div>
        <button type="button" class="btn-remove-item" onclick="removeItem(this)">✕</button>`;
    container.appendChild(item);
    updateRemoveButtons(container);
}

// ==================== CONTRADICCIONES ====================

let contradictionCount = document.querySelectorAll('#contradictionsContainer .dynamic-item').length || 0;

function addContradiction() {
    const container = document.getElementById('contradictionsContainer');
    const i = contradictionCount++;
    const item = document.createElement('div');
    item.className = 'dynamic-item contradiction-item';
    item.innerHTML = `
        <div class="item-fields">
            <div class="item-fields-row">
                <div class="item-field">
                    <label>El paciente dice... ${tooltipHTML('¿Para qué sirve?', 'Lo que el paciente afirma verbalmente.', ['"El dolor es 10/10, insoportable"'])}</label>
                    <input type="text" name="contradictions[${i}][says]" placeholder="Ej: El dolor es 10/10, insoportable">
                </div>
                <div class="item-field">
                    <label>Pero contradice porque... ${tooltipHTML('¿Para qué sirve?', 'La evidencia observable que contradice lo que dice.', ['"Vino conduciendo una moto"', '"Se ríe mientras habla"'])}</label>
                    <input type="text" name="contradictions[${i}][contradicts]" placeholder="Ej: Vino conduciendo una moto">
                </div>
            </div>
            <div class="item-fields-row">
                <div class="item-field">
                    <label>Si le pillan, dice... ${tooltipHTML('¿Para qué sirve?', 'La excusa o justificación que da si el estudiante señala la contradicción.', ['"Vine muy despacito apoyándome en las paredes"'])}</label>
                    <input type="text" name="contradictions[${i}][caught]" placeholder="Ej: Vine muy despacito apoyándome en las paredes">
                </div>
            </div>
        </div>
        <button type="button" class="btn-remove-item" onclick="removeItem(this)">✕</button>`;
    container.appendChild(item);
    updateRemoveButtons(container);
}

// ==================== EVENTOS DE CIERRE ====================

let closureCount = document.querySelectorAll('#closureContainer .dynamic-item').length || 0;

function addClosure() {
    const container = document.getElementById('closureContainer');
    const i = closureCount++;
    const item = document.createElement('div');
    item.className = 'dynamic-item closure-item';
    item.innerHTML = `
        <div class="item-fields">
            <div class="item-fields-row">
                <div class="item-field">
                    <label>Condición ${tooltipHTML('¿Para qué sirve?', 'Cuándo se activa este evento de cierre.', ['"Hacia el final de la consulta"', '"Si el alumno le da un diagnóstico"'])}</label>
                    <input type="text" name="closures[${i}][condition]" placeholder="Ej: Hacia el final de la consulta">
                </div>
                <div class="item-field">
                    <label>El paciente hace/dice... ${tooltipHTML('¿Para qué sirve?', 'La acción o frase del paciente al cerrarse la consulta.', ['"Pregunta ¿Cuál es el diagnóstico?"', '"Dice que le duele más que al principio"'])}</label>
                    <input type="text" name="closures[${i}][action]" placeholder="Ej: Pregunta '¿Cuál es el diagnóstico?'">
                </div>
            </div>
        </div>
        <button type="button" class="btn-remove-item" onclick="removeItem(this)">✕</button>`;
    container.appendChild(item);
    updateRemoveButtons(container);
}