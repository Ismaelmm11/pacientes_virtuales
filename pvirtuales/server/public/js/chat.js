/*
|--------------------------------------------------------------------------
| JS - Chat de Simulación
|--------------------------------------------------------------------------
|
| Gestiona la comunicación en tiempo real con la IA:
| - Envío de mensajes del usuario
| - Recepción y renderizado de respuestas
| - Indicador de "Escribiendo..."
|
| Requiere que CHAT_CONFIG esté definido en la vista con:
| - sendUrl: URL del endpoint de envío
| - csrfToken: Token CSRF de Laravel
| - patientName: Nombre del paciente para mostrar en mensajes
|
*/

// Selección de elementos del DOM para manipular el chat
const messagesList = document.getElementById("messagesList");
const userMessageInput = document.getElementById("userMessage");
const btnSend = document.getElementById("btnSend");
const loadingIndicator = document.getElementById("loadingIndicator");

// ==================== FUNCIONES DE UI ====================

/**
 * Añade visualmente un globo de mensaje al área de chat.
 * @param {string} text - El contenido del mensaje.
 * @param {string} role - 'user' para el médico, 'assistant' para el paciente virtual.
 */
function appendMessage(text, role) {
    const messageDiv = document.createElement("div");
    messageDiv.className = `message ${role}`; // Asigna clase CSS según quién habla

    if (role === "assistant") {
        /* Para el paciente, inyectamos el nombre configurado y usamos innerHTML 
           (útil si la respuesta de la IA trae formato básico). */
        messageDiv.innerHTML = `<span class="author">${CHAT_CONFIG.patientName}</span>${text}`;
    } else {
        /* Para el usuario, usamos innerText por seguridad (evita inyección de código). */
        messageDiv.innerText = text;
    }

    /* Inserta el mensaje justo antes del indicador de carga para que este 
       siempre quede al final mientras la IA "piensa". */
    messagesList.insertBefore(messageDiv, loadingIndicator);
    scrollToBottom();
}

/**
 * Mueve el scroll del contenedor de mensajes hasta el final.
 */
function scrollToBottom() {
    messagesList.scrollTop = messagesList.scrollHeight;
}

/**
 * Muestra el spinner o texto de "Escribiendo..."
 */
function showLoading() {
    loadingIndicator.style.display = "block";
    scrollToBottom();
}

function hideLoading() {
    loadingIndicator.style.display = "none";
}

// ==================== LÓGICA DE ENVÍO ====================

/**
 * Orquestador principal: captura el texto, lo envía al servidor y procesa la respuesta.
 */
async function sendMessage() {
    const text = userMessageInput.value.trim();
    if (!text) return; // Evita enviar mensajes vacíos

    // 1. Fase de UI inicial
    appendMessage(text, "user"); // Muestra el mensaje del usuario inmediatamente
    userMessageInput.value = ""; // Limpia el campo de texto
    userMessageInput.focus(); // Devuelve el foco para seguir escribiendo

    // 2. Bloqueo de interfaz para evitar múltiples envíos accidentales
    btnSend.disabled = true;
    showLoading();

    try {
        /* 3. Petición asíncrona al servidor Laravel */
        const response = await fetch(CHAT_CONFIG.sendUrl, {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                /* Seguridad: Laravel requiere este token para validar la petición POST */
                "X-CSRF-TOKEN": CHAT_CONFIG.csrfToken,
            },
            body: JSON.stringify({ message: text }),
        });

        const data = await response.json();

        /* Verificación de estado de la respuesta */
        if (!response.ok) {
            throw new Error(data.error || "Error de conexión con la IA");
        }

        // 4. Procesar respuesta exitosa
        hideLoading();
        appendMessage(data.response, "assistant");
    } catch (error) {
        // 5. Manejo de errores
        hideLoading();
        console.error(error);
        alert("Error: " + error.message);
    } finally {
        /* Se ejecuta siempre, asegurando que el botón se reactive */
        btnSend.disabled = false;
    }
}

// ==================== EVENT LISTENERS ====================

/* Escucha el clic en el botón de enviar */
btnSend.addEventListener("click", sendMessage);

/* Permite enviar el mensaje simplemente pulsando la tecla Enter */
userMessageInput.addEventListener("keypress", function (e) {
    if (e.key === "Enter") sendMessage();
});

// ==================== FINALIZAR CONSULTA (ALUMNO) ====================

function openFarewellModal() {
    document.getElementById("farewellOverlay").classList.add("active");
    document.getElementById("farewellInput").focus();
}

function closeFarewellModal() {
    document.getElementById("farewellOverlay").classList.remove("active");
}

async function sendFarewell() {
    const text = document.getElementById("farewellInput").value.trim();
    if (!text) return;

    // Cerrar modal y bloquear interfaz
    closeFarewellModal();
    document.getElementById("btnFarewellSend").disabled = true;

    // Enviar el mensaje de despedida como un mensaje normal
    appendMessage(text, "user");
    showLoading();

    // Bloquear el input principal
    userMessageInput.disabled = true;
    btnSend.disabled = true;
    document.getElementById("btnFinish").disabled = true;

    try {
        const response = await fetch(CHAT_CONFIG.sendUrl, {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": CHAT_CONFIG.csrfToken,
            },
            body: JSON.stringify({ message: text, is_farewell: true }),
        });

        const data = await response.json();

        if (!response.ok) throw new Error(data.error || "Error de conexión");

        hideLoading();
        appendMessage(data.response, "assistant");
    } catch (error) {
        hideLoading();
        console.error(error);
        alert("Error: " + error.message);
    } finally {
        // Consulta terminada: ocultar botón finalizar, mostrar ir al cuestionario
        document.getElementById("btnFinish").classList.add("chat-hidden");
        document.getElementById("btnGoTest").classList.add("active");
    }
}

// Cerrar modal al pulsar fuera
document
    .getElementById("farewellOverlay")
    ?.addEventListener("click", function (e) {
        if (e.target === this) closeFarewellModal();
    });

/* Asegura que al abrir el chat, el usuario vea los últimos mensajes (historial) */
scrollToBottom();
