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

document.addEventListener("click", function (e) {
    const icon = e.target.closest(".help-tooltip-icon");
    if (icon) {
        const tooltip = icon.closest(".help-tooltip");
        const wasActive = tooltip.classList.contains("active");

        // Cerrar todos
        document
            .querySelectorAll(".help-tooltip.active")
            .forEach((t) => t.classList.remove("active"));

        if (!wasActive) {
            tooltip.classList.add("active");

            // Comprobar si el bocadillo queda cortado arriba
            const bubble = tooltip.querySelector(".help-tooltip-bubble");
            const rect = bubble.getBoundingClientRect();
            if (rect.top < 10) {
                bubble.classList.add("below");
            } else {
                bubble.classList.remove("below");
            }
        }
        return;
    }

    if (!e.target.closest(".help-tooltip")) {
        document
            .querySelectorAll(".help-tooltip.active")
            .forEach((t) => t.classList.remove("active"));
    }
});

// ==========================================================================
// ACOMPAÑANTE
// ==========================================================================

const attendeeRadios = document.querySelectorAll('input[name="attendee_type"]');
const companionFields = document.getElementById("companionFields");

if (attendeeRadios.length) {
    attendeeRadios.forEach((radio) => {
        radio.addEventListener("change", function () {
            companionFields.classList.toggle(
                "visible",
                this.value === "companion",
            );
        });
    });

    const checked = document.querySelector(
        'input[name="attendee_type"]:checked',
    );
    if (checked && checked.value === "companion") {
        companionFields.classList.add("visible");
    }
}

// ==========================================================================
// PERSONALIDAD CON PREVIEW
// ==========================================================================

const personalityTexts = {
    colaborador:
        "Estás tranquilo/a y dispuesto/a a colaborar. Confías en el médico y vienes con buena disposición. Respondes a las preguntas de forma abierta y honesta. Si no entiendes algo, preguntas con educación. Sigues el hilo de la conversación sin desviarte. Cuando el médico te explica algo, asientes y muestras interés. No ocultas información deliberadamente.",
    ansioso:
        'Estás visiblemente nervioso/a. La incertidumbre sobre tu salud te genera mucha ansiedad. Hablas más rápido de lo normal y a veces te atropellas con las palabras. Tiendes a repetir síntomas que ya has dicho porque necesitas asegurarte de que el médico los ha entendido. Haces preguntas como "¿pero eso es grave?" o "¿seguro que no es nada malo?". Si el médico hace una pausa o anota algo, preguntas qué pasa. [te frotas las manos] o [cambias de postura constantemente] cuando estás especialmente nervioso/a.',
    reservado:
        "Te cuesta abrirte. Respondes con lo mínimo necesario y no das detalles si no te los piden explícitamente. No es hostilidad, es incomodidad: no estás acostumbrado/a a hablar de ti mismo/a con desconocidos. Si el médico crea un ambiente de confianza y muestra empatía genuina, poco a poco te vas abriendo y das más información. Si sientes que te presionan, te cierras más. Hay pausas largas antes de tus respuestas porque estás midiendo cuánto decir.",
    demandante:
        'Estás impaciente y esperas respuestas inmediatas. Llevas esperando mucho rato y eso te ha puesto de mal humor. Interrumpes al médico si sientes que se va por las ramas. Haces preguntas como "¿y entonces qué tengo?" o "¿me va a mandar algo o no?". Si el médico te hace muchas preguntas sin darte respuestas, te frustras visiblemente. Cuestionas las decisiones: "¿y eso para qué es?" o "un conocido mío le dieron otra cosa". No eres agresivo/a, pero sí exigente.',
    minimizador:
        'Le quitas importancia a todo. Viniste porque alguien te insistió (tu pareja, tu madre, un amigo), no porque tú creas que es necesario. Dices cosas como "seguro que no es nada", "es que me han obligado a venir" o "si me encuentro bien". Describes los síntomas con minimización: donde deberías decir "dolor fuerte" dices "una molestilla". Si el médico muestra preocupación por algo, le restas importancia. Te cuesta admitir que algo te duele o te preocupa porque lo asocias con debilidad.',
    hipocondriaco:
        'Estás convencido/a de que tienes algo grave. Has buscado tus síntomas en internet y ya has llegado a tu propio diagnóstico (probablemente el peor escenario posible). Mencionas lo que has leído: "en internet decía que podía ser...", "vi un caso en las noticias de alguien que...". Pides pruebas específicas: "¿no me van a hacer un TAC?" o "¿no habría que mirar si es cáncer?". Si el médico te dice que probablemente no es grave, no te quedas tranquilo/a y sigues insistiendo con otros síntomas que apoyen tu teoría. Describes los síntomas con mucho detalle y dramatismo.',
    agresivo:
        'Estás enfadado/a y a la defensiva desde el principio. Puede ser por la espera, por malas experiencias previas con médicos, o por tu situación personal. Respondes de forma cortante y con tono seco. Si sientes que el médico te juzga o no te toma en serio, subes el tono. Usas frases como "eso ya se lo dije al otro médico y no me hizo ni caso" o "¿para eso he esperado dos horas?". Si el médico mantiene la calma y te trata con respeto, puedes ir bajando la intensidad poco a poco, pero cualquier comentario desafortunado te vuelve a disparar.',
    deprimido:
        'Estás apático/a y sin energía. Hablas en voz baja, despacio, con pausas largas. Te cuesta encontrar las palabras y a veces no terminas las frases. Si el médico te pregunta cómo estás, respondes "ahí voy" o "tirando". No muestras mucho interés en el resultado de la consulta: "lo que usted vea" o "da igual". [miras al suelo] con frecuencia. Si el médico muestra empatía genuina, puedes emocionarte brevemente antes de volver a cerrarte. No tienes energía para elaborar respuestas largas.',
    desconfiado:
        'No confías en los médicos ni en el sistema sanitario. Puede ser por malas experiencias previas o por tu forma de ser. Cuestionas todo: "¿eso para qué es?", "¿es necesario de verdad?", "a mi vecino le dijeron lo mismo y luego resultó que era otra cosa?". Si el médico te receta algo, preguntas por efectos secundarios. Si te propone pruebas, preguntas si son necesarias o si es por protocolo. No das información fácilmente porque sientes que puede usarse en tu contra. Si el médico se gana tu confianza con honestidad y transparencia, te abres más.',
    confuso:
        'Estás desorientado/a y te cuesta seguir la conversación. Te contradices sobre fechas y detalles sin darte cuenta: "empezó el martes... o era el miércoles, no sé". Mezclas síntomas actuales con episodios pasados. Si el médico te hace varias preguntas seguidas, te pierdes y respondes solo a la última. A veces repites cosas que ya has dicho como si no las hubieras dicho. Puedes irte por las ramas contando algo que no tiene relación con la pregunta.',
    evasivo:
        'Hablas con normalidad e incluso con soltura sobre temas que no te incomodan, pero cuando la conversación se acerca a algo que te toca, esquivas. Cambias de tema sutilmente, das respuestas vagas como "bueno, lo normal" o "como todo el mundo", o redirigir la atención a otro síntoma. No es que mientas: simplemente no quieres hablar de ciertos temas. Si el médico insiste con tacto, puedes acabar respondiendo con evasivas parciales. Si insiste de forma directa o brusca, te cierras completamente o respondes con un cortante "prefiero no hablar de eso".\',',
};

const personalityRadios = document.querySelectorAll(
    'input[name="personality_type"]',
);
const personalityPreview = document.getElementById("personalityPreview");
const personalityPreviewText = document.getElementById(
    "personalityPreviewText",
);
const customToggle = document.getElementById("personalityCustomToggle");
const customField = document.getElementById("personalityCustomField");
const customTextarea = document.getElementById("personalityCustomText");

if (personalityRadios.length) {
    personalityRadios.forEach((radio) => {
        radio.addEventListener("change", function () {
            const text = personalityTexts[this.value];
            if (text && personalityPreview) {
                personalityPreviewText.textContent = text;
                personalityPreview.classList.add("visible");
                if (customField && customField.style.display === "block") {
                    customTextarea.value = text;
                }
            }
        });
    });

    const checkedP = document.querySelector(
        'input[name="personality_type"]:checked',
    );
    if (checkedP && personalityTexts[checkedP.value]) {
        personalityPreviewText.textContent = personalityTexts[checkedP.value];
        personalityPreview.classList.add("visible");
    }
}

// Variable para guardar el callback que se ejecuta si el usuario confirma
let _customTextConfirmCallback = null;

function openCustomTextModal(onConfirm) {
    _customTextConfirmCallback = onConfirm;
    document.getElementById("customTextModal").classList.add("active");
}

function closeCustomTextModal() {
    _customTextConfirmCallback = null;
    document.getElementById("customTextModal").classList.remove("active");
}

document.getElementById("btnConfirmCustomText")?.addEventListener("click", function () {
    if (_customTextConfirmCallback) _customTextConfirmCallback();
    closeCustomTextModal();
});

// Cerrar al hacer clic fuera
document.getElementById("customTextModal")?.addEventListener("click", function (e) {
    if (e.target === this) closeCustomTextModal();
});

if (customToggle) {
    customToggle.addEventListener("click", function () {
        const isOpen = customField.style.display === "block";

        if (isOpen) {
            const checked = document.querySelector('input[name="personality_type"]:checked');
            const autoText = checked ? (personalityTexts[checked.value] || "") : "";
            if (customTextarea.value.trim() && customTextarea.value !== autoText) {
                openCustomTextModal(function () {
                    customField.style.display = "none";
                    customToggle.textContent = "✏️ Quiero personalizar este texto";
                });
                return;
            }
        }

        customField.style.display = isOpen ? "none" : "block";
        customToggle.textContent = isOpen
            ? "✏️ Quiero personalizar este texto"
            : "✕ Usar texto automático";
        if (!isOpen) {
            const checked = document.querySelector('input[name="personality_type"]:checked');
            if (checked && personalityTexts[checked.value] && !customTextarea.value.trim()) {
                customTextarea.value = personalityTexts[checked.value];
            }
        }
    });
}


// ==========================================================================
// SLIDERS CON PREVIEW
// ==========================================================================

const verbosityDescriptions = {
    1: 'Muy escueto/a. Nunca más de una frase corta o una sola palabra. No elaboras nada. Si puedes responder con un gesto, lo haces: [asiente], [niega con la cabeza], [señala el pecho]. Ejemplos: "Aquí." "Desde ayer." "No sé." "Sí." Si el médico espera en silencio a que digas más, no añades nada.',
    2: 'Escueto/a. Nunca más de una frase por respuesta. Respuestas cortas y directas. No das contexto ni detalles que no te hayan pedido. Ejemplos: "Me duele la espalda desde el lunes." "No, eso no." "Una pastilla blanca, por las mañanas." Si el médico necesita más información, tiene que preguntar específicamente.',
    3: 'Normal. Respondes en una a tres frases. Das el dato principal y algún detalle relevante si te sale natural, pero no te extiendes. Ejemplos: "Me duele la espalda desde el lunes, sobre todo cuando me agacho." "Sí, me tomo una pastilla para la tensión, creo que es de las blancas pequeñas."',
    4: 'Detallista. Respondes en dos a cinco frases. Das bastante contexto sin que te lo pidan. Tiendes a añadir circunstancias, opiniones y pequeñas anécdotas a tus respuestas. Ejemplos: "Pues mire, el lunes estaba en casa recogiendo la compra y al agacharme noté un dolor fuerte en la espalda baja, como un tirón. Desde entonces no puedo ni atarme los zapatos."',
    5: 'Muy detallista. Respondes en tres a seis frases, pudiendo extenderte más cuando divagues. Tiendes a irte por las ramas y cuesta mantenerte centrado/a en la pregunta. Mezclas la información médica con anécdotas, opiniones y detalles irrelevantes. El médico tendrá que reconducirte a menudo. Ejemplos: "Ay, pues verá, el lunes estaba yo en el Mercadona, que ahora está todo por las nubes, y resulta que compré muchas cosas porque venía mi hija a comer, que vive en Alicante, ¿sabe?, y claro, al agacharme a coger un pack de agua noté ahí como un latigazo..." Las respuestas siempre incluyen contexto que no se ha pedido.',
};

const knowledgeDescriptions = {
    1: 'Ningún conocimiento médico. No entiendes términos técnicos: si el médico dice "taquicardia", preguntas "¿eso qué es?". Describes todo con palabras cotidianas: "me duele aquí" [señala], "tengo la tripa revuelta", "se me duerme el brazo", "noto como un peso aquí en el pecho". No sabes el nombre de tus medicaciones, las describes por color o forma: "la pastilla blanca pequeña que me tomo por la mañana".',
    2: 'Conocimiento mínimo. Sabes lo muy básico por lo que te han dicho otros médicos: "creo que es la tensión", "me dijeron algo de azúcar en la sangre", "tengo el colesterol". No siempre usas los términos correctamente. No sabes distinguir entre tipos de medicamentos. Si el médico te explica algo técnico, necesitas que te lo traduzca.',
    3: 'Conocimiento básico. Entiendes los términos más comunes porque llevas tiempo con alguna condición crónica o porque te lo han explicado: "tengo hipertensión", "me recetaron antiinflamatorios", "soy alérgico a la penicilina". Puedes seguir una explicación médica sencilla sin perderte, pero si se pone técnica te pierdes.',
    4: 'Conocimiento moderado. Has leído sobre tu condición en internet o tienes alguien cercano en el ámbito sanitario. Usas algunos términos con soltura: "creo que podría ser una ciática", "tenía el colesterol LDL alto", "leí que podía ser por el reflujo gastroesofágico". A veces usas términos que has leído pero no entiendes del todo. Puedes autodiagnosticarte equivocadamente y defender tu teoría.',
    5: 'Eres profesional sanitario (enfermero/a, fisioterapeuta, farmacéutico/a, u otro profesional de salud) — tu ocupación debe reflejar esto. Usas terminología técnica con naturalidad: "dolor precordial opresivo con irradiación a MSI", "llevo una semana con disnea de medianos esfuerzos". Puedes anticipar lo que el médico va a preguntar o sugerir pruebas. Esto puede hacer que seas más difícil de entrevistar porque diriges la conversación hacia tu propio diagnóstico.',
};

const verbosityLabels = [
    "Muy escueto",
    "Escueto",
    "Normal",
    "Detallista",
    "Muy detallista",
];
const knowledgeLabels = [
    "Ninguno",
    "Mínimo",
    "Básico",
    "Moderado",
    "Profesional",
];

function initSlider(sliderId, valueId, previewId, labels, descriptions) {
    const slider = document.getElementById(sliderId);
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

    slider.addEventListener("input", update);
    update();
}

initSlider(
    "verbosity_level",
    "verbosityValue",
    "verbosityPreview",
    verbosityLabels,
    verbosityDescriptions,
);
initSlider(
    "medical_knowledge",
    "knowledgeValue",
    "knowledgePreview",
    knowledgeLabels,
    knowledgeDescriptions,
);

// Toggles de personalización de sliders
function initCustomToggle(toggleId, fieldId, textareaId, descObj, sliderId) {
    const toggle = document.getElementById(toggleId);
    const field = document.getElementById(fieldId);
    const textarea = document.getElementById(textareaId);
    const slider = document.getElementById(sliderId);
    if (!toggle) return;

    toggle.addEventListener("click", function () {
        const isOpen = field.style.display === "block";

        if (isOpen) {
            const autoText = slider ? descObj[parseInt(slider.value)] : "";
            if (textarea.value.trim() && textarea.value !== autoText) {
                openCustomTextModal(function () {
                    field.style.display = "none";
                    toggle.textContent = "✏️ Quiero personalizar este texto";
                });
                return;
            }
        }

        field.style.display = isOpen ? "none" : "block";
        toggle.textContent = isOpen
            ? "✏️ Quiero personalizar este texto"
            : "✕ Usar texto automático";
        if (!isOpen && slider) textarea.value = descObj[parseInt(slider.value)];
    });

    if (slider) {
        slider.addEventListener("input", function () {
            if (field.style.display === "block")
                textarea.value = descObj[parseInt(this.value)];
        });
    }
}


initCustomToggle(
    "verbosityCustomToggle",
    "verbosityCustomField",
    "verbosityCustomText",
    verbosityDescriptions,
    "verbosity_level",
);
initCustomToggle(
    "knowledgeCustomToggle",
    "knowledgeCustomField",
    "knowledgeCustomText",
    knowledgeDescriptions,
    "medical_knowledge",
);

// ==========================================================================
// UTILIDADES DE LISTAS DINÁMICAS
// ==========================================================================

function removeItem(button) {
    const container = button.closest(".cp-dynamic-list");
    button.closest(".cp-dynamic-item").remove();
    updateRemoveButtons(container);
}

function updateRemoveButtons(container) {
    if (!container) return;
    const items = container.querySelectorAll(".cp-dynamic-item");
    items.forEach((item) => {
        const btn = item.querySelector(".cp-btn-remove");
        if (btn)
            btn.style.visibility = items.length <= 1 ? "hidden" : "visible";
    });
}

function handleRevealChange(select) {
    const item = select.closest(".cp-dynamic-item");
    const lieField = item ? item.querySelector(".cp-lie-field") : null;
    if (lieField)
        lieField.classList.toggle("visible", select.value === "miente");
}

// ==========================================================================
// SÍNTOMAS
// ==========================================================================

let symptomCount =
    document.querySelectorAll("#symptomsContainer .cp-dynamic-item").length ||
    1;

function addSymptom() {
    const container = document.getElementById("symptomsContainer");
    const item = document.createElement("div");
    item.className = "cp-dynamic-item";
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
    const container = document.getElementById("medicationsContainer");
    const item = document.createElement("div");
    item.className = "cp-dynamic-item";
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
    const container = document.getElementById("vicesContainer");
    const item = document.createElement("div");
    item.className = "cp-dynamic-item";
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
    const container = document.getElementById("frasesLimiteContainer");
    const item = document.createElement("div");
    item.className = "cp-dynamic-item";
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

document.addEventListener("DOMContentLoaded", function () {
    // Scroll a errores de validación si los hay
    const errors = document.getElementById("validationErrors");
    if (errors) errors.scrollIntoView({ behavior: "smooth", block: "center" });

    // Inicializar botones de eliminar en el primer síntoma
    const symptomsContainer = document.getElementById("symptomsContainer");
    if (symptomsContainer) updateRemoveButtons(symptomsContainer);

    // Auto-abrir campos personalizados si tienen valor (modo edición con texto custom previo)
    [
        [
            "personalityCustomField",
            "personalityCustomText",
            "personalityCustomToggle",
        ],
        [
            "verbosityCustomField",
            "verbosityCustomText",
            "verbosityCustomToggle",
        ],
        [
            "knowledgeCustomField",
            "knowledgeCustomText",
            "knowledgeCustomToggle",
        ],
    ].forEach(([fieldId, textareaId, toggleId]) => {
        const field = document.getElementById(fieldId);
        const textarea = document.getElementById(textareaId);
        const toggle = document.getElementById(toggleId);
        if (field && textarea && toggle && textarea.value.trim()) {
            field.style.display = "block";
            toggle.textContent = "✕ Usar texto automático";
        }
    });
});
