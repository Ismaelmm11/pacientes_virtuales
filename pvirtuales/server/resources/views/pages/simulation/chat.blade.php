<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Necesario para la seguridad de Laravel en AJAX -->
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Consulta con {{ $patient['name'] }}</title>
    <link href="{{ asset('css/style.css') }}" rel="stylesheet">
    <link href="{{ asset('css/chat.css') }}" rel="stylesheet">
</head>
<body>

    <div class="chat-container">
        <!-- Cabecera -->
        <div class="chat-header">
            <div class="patient-info">
                <h2>Paciente: {{ $patient['name'] }}</h2>
                <span>Simulación Activa</span>
            </div>
            <div style="display: flex; align-items: center; gap: 15px;">
                <span class="ai-badge bg-{{ $aiModel }}">IA: {{ strtoupper($aiModel) }}</span>
                <a href="/pacientes_virtuales" class="btn-exit">Finalizar Consulta ✕</a>
            </div>
        </div>

        <!-- Área de Mensajes -->
        <div class="chat-messages" id="messagesList">
            
            <!-- Renderizamos el historial inicial (el mensaje de bienvenida) -->
            @foreach($history as $message)
                <!-- Ignoramos el mensaje 'system' porque es invisible para el usuario -->
                @if($message['role'] != 'system')
                    <div class="message {{ $message['role'] }}">
                        @if($message['role'] == 'assistant')
                            <span class="author">{{ $patient['name'] }}</span>
                        @endif
                        {{ $message['content'] }}
                    </div>
                @endif
            @endforeach
            
            <!-- Elemento invisible para el indicador de "Escribiendo..." -->
            <div id="loadingIndicator" class="message assistant" style="display: none;">
                <span class="author">{{ $patient['name'] }}</span>
                <span class="typing-dots">Escribiendo...</span>
            </div>

        </div>

        <!-- Área de Input -->
        <div class="chat-input-area">
            <input type="text" class="chat-input" id="userMessage" placeholder="Escribe tu pregunta al paciente..." autocomplete="off">
            <button class="btn-send" id="btnSend">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="22" y1="2" x2="11" y2="13"></line><polygon points="22 2 15 22 11 13 2 9 22 2"></polygon></svg>
            </button>
        </div>
    </div>

    <script>
        const messagesList = document.getElementById('messagesList');
        const userMessageInput = document.getElementById('userMessage');
        const btnSend = document.getElementById('btnSend');
        const loadingIndicator = document.getElementById('loadingIndicator');
        
        // Obtener el token CSRF para seguridad
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        function appendMessage(text, role) {
            const messageDiv = document.createElement('div');
            messageDiv.className = `message ${role}`;
            
            if (role === 'assistant') {
                // Añadir nombre del paciente si es la IA
                messageDiv.innerHTML = `<span class="author">{{ $patient['name'] }}</span>${text}`;
            } else {
                messageDiv.innerText = text;
            }
            
            // Insertar antes del indicador de carga
            messagesList.insertBefore(messageDiv, loadingIndicator);
            scrollToBottom();
        }

        function scrollToBottom() {
            messagesList.scrollTop = messagesList.scrollHeight;
        }

        function showLoading() {
            loadingIndicator.style.display = 'block';
            scrollToBottom();
        }

        function hideLoading() {
            loadingIndicator.style.display = 'none';
        }

        // --- LÓGICA DE ENVÍO ---
        async function sendMessage() {
            const text = userMessageInput.value.trim();
            if (!text) return;

            // 1. Mostrar mensaje del usuario
            appendMessage(text, 'user');
            userMessageInput.value = '';
            userMessageInput.focus();
            
            // 2. Bloquear botón y mostrar "Escribiendo..."
            btnSend.disabled = true;
            showLoading();

            try {
                // 3. LLAMADA AL SERVIDOR (La magia)
                const response = await fetch("{{ route('simulation.send') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: JSON.stringify({ message: text })
                });

                const data = await response.json();

                if (!response.ok) {
                    throw new Error(data.error || 'Error de conexión con la IA');
                }

                // 4. Mostrar respuesta de la IA
                hideLoading();
                appendMessage(data.response, 'assistant');

            } catch (error) {
                hideLoading();
                console.error(error);
                alert("Error: " + error.message);
                // Opcional: Mostrar mensaje de error en el chat en rojo
            } finally {
                btnSend.disabled = false;
            }
        }

        btnSend.addEventListener('click', sendMessage);

        userMessageInput.addEventListener('keypress', (e) => {
            if (e.key === 'Enter') sendMessage();
        });

        // Scroll al fondo al cargar
        scrollToBottom();
    </script>

</body>
</html>