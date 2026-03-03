/**
 * patient-test.js
 *
 * Gestiona la interactividad del formulario de creación de preguntas:
 * - Mostrar/ocultar campos según el tipo de pregunta
 * - Añadir/eliminar opciones en múltiple elección
 * - Actualizar visibilidad de botones de eliminar
 */

// === CAMBIO DE TIPO DE PREGUNTA ===

function handleTypeChange() {
    const type = document.getElementById('question_type').value;

    const mcFields       = document.getElementById('multipleChoiceFields');
    const tfFields       = document.getElementById('trueFalseFields');
    const feedbackFields = document.getElementById('feedbackFields');
    const openEndedInfo  = document.getElementById('openEndedInfo');

    // Ocultar todo primero
    mcFields.style.display       = 'none';
    tfFields.style.display       = 'none';
    feedbackFields.style.display = 'none';
    openEndedInfo.style.display  = 'none';

    // Desactivar inputs ocultos para que no se envíen
    disableInputs(mcFields);
    disableInputs(tfFields);

    switch (type) {
        case 'MULTIPLE_CHOICE':
            mcFields.style.display       = 'block';
            feedbackFields.style.display = 'block';
            enableInputs(mcFields);
            break;

        case 'TRUE_FALSE':
            tfFields.style.display       = 'block';
            feedbackFields.style.display = 'block';
            enableInputs(tfFields);
            break;

        case 'OPEN_ENDED':
            openEndedInfo.style.display = 'block';
            break;
    }
}

function disableInputs(container) {
    container.querySelectorAll('input, select, textarea').forEach(el => {
        el.disabled = true;
    });
}

function enableInputs(container) {
    container.querySelectorAll('input, select, textarea').forEach(el => {
        el.disabled = false;
    });
}

// === OPCIONES DE MÚLTIPLE ELECCIÓN ===

let optionCount = 2; // Empezamos con 2 opciones
const MAX_OPTIONS = 6;

function addOption() {
    if (optionCount >= MAX_OPTIONS) return;

    const container = document.getElementById('optionsContainer');
    const letters = ['A', 'B', 'C', 'D', 'E', 'F'];

    const row = document.createElement('div');
    row.className = 'option-row';
    row.innerHTML = `
        <input type="text" name="options[]" placeholder="Opción ${letters[optionCount]}">
        <button type="button" class="btn-remove-option" onclick="removeOption(this)">✕</button>
    `;

    container.appendChild(row);
    optionCount++;

    updateOptionButtons();
}

function removeOption(btn) {
    if (optionCount <= 2) return; // Mínimo 2 opciones

    btn.closest('.option-row').remove();
    optionCount--;

    updateOptionButtons();
}

function updateOptionButtons() {
    const btnAdd = document.getElementById('btnAddOption');
    btnAdd.style.display = optionCount >= MAX_OPTIONS ? 'none' : 'inline-flex';

    // Mostrar/ocultar botones de eliminar (ocultar si solo quedan 2)
    const rows = document.querySelectorAll('#optionsContainer .option-row');
    rows.forEach(row => {
        const removeBtn = row.querySelector('.btn-remove-option');
        if (removeBtn) {
            removeBtn.style.visibility = rows.length <= 2 ? 'hidden' : 'visible';
        }
    });
}

// === INICIALIZACIÓN ===

document.addEventListener('DOMContentLoaded', function () {
    // Aplicar estado inicial según el tipo seleccionado (por si hay old())
    handleTypeChange();

    // Contar opciones existentes (por si hay old())
    optionCount = document.querySelectorAll('#optionsContainer .option-row').length;
    updateOptionButtons();
});