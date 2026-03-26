/*
|--------------------------------------------------------------------------
| JS — Gestión del Test (Vista Profesor)
|--------------------------------------------------------------------------
*/

// ==================== TIPO DE PREGUNTA ====================

function switchQuestionType(type) {
    // Ocultar todos los bloques de campos
    document.querySelectorAll(".cp-question-fields").forEach((el) => {
        el.classList.remove("visible");
    });

    // Mostrar el bloque del tipo seleccionado
    const target = document.getElementById("fields_" + type);
    if (target) target.classList.add("visible");

    // Si es opción múltiple, actualizar el select de respuesta correcta
    if (type === "MULTIPLE_CHOICE") updateCorrectAnswerSelect();
}

// ==================== OPCIONES MÚLTIPLE ====================

function addOption() {
    const list = document.getElementById("optionsList");
    const items = list.querySelectorAll(".cp-option-item");

    if (items.length >= 6) return;

    const index = items.length;
    const letter = String.fromCharCode(65 + index);

    const div = document.createElement("div");
    div.className = "cp-option-item";
    div.innerHTML = `
        <span class="cp-option-letter">${letter}</span>
        <input type="text" name="options[]" placeholder="Opción ${letter}">
        <button type="button" class="cp-btn-remove" onclick="removeOption(this)">
            <i data-lucide="x"></i>
        </button>
    `;

    list.appendChild(div);
    lucide.createIcons();
    updateOptionLetters();
    updateCorrectAnswerSelect();

    // Ocultar botón si llegamos a 6
    if (items.length + 1 >= 6) {
        document.getElementById("btnAddOption").style.display = "none";
    }
}

function removeOption(btn) {
    const list = document.getElementById("optionsList");
    const items = list.querySelectorAll(".cp-option-item");
    if (items.length <= 2) return; // Mínimo 2 opciones

    btn.closest(".cp-option-item").remove();
    updateOptionLetters();
    updateCorrectAnswerSelect();
    document.getElementById("btnAddOption").style.display = "inline-flex";
}

function updateOptionLetters() {
    const items = document.querySelectorAll("#optionsList .cp-option-item");
    items.forEach((item, i) => {
        const letter = String.fromCharCode(65 + i);
        item.querySelector(".cp-option-letter").textContent = letter;
        item.querySelector("input").placeholder = "Opción " + letter;

        // Mostrar/ocultar botón eliminar (mínimo 2)
        const btn = item.querySelector(".cp-btn-remove");
        btn.style.visibility = items.length > 2 ? "visible" : "hidden";
    });
}

function updateCorrectAnswerSelect() {
    const select = document.getElementById("correct_answer_mc");
    const inputs = document.querySelectorAll(
        "#optionsList .cp-option-item input",
    );
    const current = select.value;

    select.innerHTML =
        '<option value="">Selecciona la respuesta correcta...</option>';

    inputs.forEach((input, i) => {
        const letter = String.fromCharCode(65 + i);
        const text = input.value.trim() || "Opción " + letter;
        const option = document.createElement("option");
        option.value = text;
        option.textContent = letter + ". " + text;
        if (text === current) option.selected = true;
        select.appendChild(option);
    });

    // Actualizar select cuando cambia el texto de una opción
    inputs.forEach((input) => {
        input.addEventListener("input", updateCorrectAnswerSelect);
    });
}

// ==================== ALEATORIEDAD ====================

function toggleRandomConfig(show) {
    const config = document.getElementById("randomConfig");
    config.classList.toggle("visible", show);
    document.getElementById("requiredField").classList.toggle("visible", show);
    document.getElementById("questions_per_test").required = show; // añadir esta línea
    if (!show) {
        document.getElementById("questions_per_test").value = "";
    }
}

// ==================== RESET ====================

function resetForm() {
    document.querySelectorAll(".cp-question-fields").forEach((el) => {
        el.classList.remove("visible");
    });
    document.querySelectorAll('input[name="question_type"]').forEach((el) => {
        el.checked = false;
    });
}

// ==================== EDICIÓN DE PREGUNTA ====================

function loadQuestionForEdit(data) {
    const form = document.getElementById("questionForm");

    // Cambiar acción y añadir _method=PUT
    form.action = form.dataset.storeUrl + "/" + data.id;
    let methodInput = document.getElementById("formMethod");
    if (!methodInput) {
        methodInput = document.createElement("input");
        methodInput.type = "hidden";
        methodInput.name = "_method";
        methodInput.id = "formMethod";
        form.appendChild(methodInput);
    }
    methodInput.value = "PUT";

    // Tipo de pregunta
    const typeRadio = document.querySelector(
        `input[name="question_type"][value="${data.question_type}"]`,
    );
    if (typeRadio) {
        typeRadio.checked = true;
        switchQuestionType(data.question_type);
    }

    // Enunciado
    document.getElementById("question_text").value = data.question_text || "";

    // Opción múltiple: reconstruir opciones y seleccionar respuesta
    if (data.question_type === "MULTIPLE_CHOICE" && data.options) {
        const list = document.getElementById("optionsList");
        list.innerHTML = "";
        data.options.forEach((opt, i) => {
            const letter = String.fromCharCode(65 + i);
            const div = document.createElement("div");
            div.className = "cp-option-item";
            div.innerHTML = `<span class="cp-option-letter">${letter}</span>
                <input type="text" name="options[]" value="${opt}">
                <button type="button" class="cp-btn-remove" onclick="removeOption(this)">
                    <i data-lucide="x"></i>
                </button>`;
            list.appendChild(div);
        });
        lucide.createIcons();
        updateOptionLetters();
        updateCorrectAnswerSelect();
        document.getElementById("correct_answer_mc").value =
            data.correct_answer || "";
        document.getElementById("btnAddOption").style.display =
            data.options.length >= 6 ? "none" : "inline-flex";
    }

    // Verdadero/Falso
    if (data.question_type === "TRUE_FALSE") {
        const tfRadio = document.querySelector(
            `input[name="correct_answer"][value="${data.correct_answer}"]`,
        );
        if (tfRadio) tfRadio.checked = true;
    }

    // Feedback
    document
        .querySelectorAll('[name="feedback_correct"]')
        .forEach((el) => (el.value = data.feedback_correct || ""));
    document
        .querySelectorAll('[name="feedback_incorrect"]')
        .forEach((el) => (el.value = data.feedback_incorrect || ""));

    // Obligatoria
    const reqVal = data.is_required ? "1" : "0";
    const reqRadio = document.querySelector(
        `input[name="is_required"][value="${reqVal}"]`,
    );
    if (reqRadio) reqRadio.checked = true;

    // Actualizar UI
    document.getElementById("questionFormTitle").textContent =
        "Editar Pregunta";
    document.getElementById("btnCancelEdit").style.display = "inline-flex";
    document.getElementById("btnSubmitQuestion").innerHTML =
        '<i data-lucide="save"></i> Guardar Cambios';
    lucide.createIcons();

    form.scrollIntoView({ behavior: "smooth", block: "start" });
}

function cancelEdit() {
    const form = document.getElementById("questionForm");
    form.action = form.dataset.storeUrl;

    const methodInput = document.getElementById("formMethod");
    if (methodInput) methodInput.remove();

    document.getElementById("questionFormTitle").textContent =
        "Añadir Pregunta";
    document.getElementById("btnCancelEdit").style.display = "none";
    document.getElementById("btnSubmitQuestion").innerHTML =
        '<i data-lucide="plus"></i> Añadir Pregunta';
    lucide.createIcons();

    form.reset();
    resetForm();
}

// Función para mostrar/ocultar el campo de intentos según el checkbox de ilimitados
function toggleUnlimitedAttempts(checkbox) {
    const numInput      = document.getElementById('max_attempts');
    const hiddenInput   = document.getElementById('max_attempts_unlimited');

    if (checkbox.checked) {
        // Ilimitados: desactiva el number input, activa el hidden -1
        numInput.disabled = true;
        numInput.hidden   = true;
        hiddenInput.disabled = false;
    } else {
        // Limitados: activa el number input, desactiva el hidden
        numInput.disabled = false;
        numInput.hidden   = false;
        hiddenInput.disabled = true;
    }
}


// ==================== INIT ====================

// Si hay errores de validación y se seleccionó un tipo, restaurarlo
const oldType = document.querySelector('input[name="question_type"]:checked');
if (oldType) switchQuestionType(oldType.value);
