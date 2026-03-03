{{--
|--------------------------------------------------------------------------
| Chat de Simulación de Consulta Médica
|--------------------------------------------------------------------------
|
| Interfaz de usuario final donde se desarrolla el roleplay.
| Maneja el historial de mensajes, estados de carga y envío asíncrono.
|
--}}
<x-layouts.app title="Consulta con {{ $patient['name'] }}">
    <x-slot:styles>
        {{-- Estilos específicos para la burbujas de chat y el área de entrada --}}
        <link href="{{ asset('css/chat.css') }}" rel="stylesheet">
    </x-slot:styles>

    <div class="chat-container">
        {{-- Cabecera: Información contextual de la sesión actual --}}
        <div class="chat-header">
            <div class="patient-info">
                <h2>Paciente: {{ $patient['name'] }}</h2>
                <span>Simulación Activa</span>
            </div>
            <div style="display: flex; align-items: center; gap: 15px;">
                {{-- Identificador visual del modelo de IA que está "poseyendo" al paciente --}}
                <span class="ai-badge bg-{{ $aiModel }}">IA: {{ strtoupper($aiModel) }}</span>
                {{-- Salida segura para finalizar la sesión --}}
                <a href="{{ route('patients.test.take', $patient['id']) }}" class="btn-exit"
                    onclick="return confirm('¿Seguro que quieres finalizar la consulta e ir al test?')">
                    Finalizar y Hacer Test
                </a>
            </div>
        </div>

        {{-- Área de Mensajes: El contenedor del historial --}}
        <div class="chat-messages" id="messagesList">
            {{--
            Bucle de historial:
            Permite que el usuario vea los mensajes anteriores si recarga la página.
            --}}
            @foreach($history as $message)
                {{--
                FILTRO DE SEGURIDAD:
                Nunca mostramos el mensaje con rol 'system' (el prompt de instrucciones),
                ya que contiene la "verdad médica" que el alumno debe descubrir.
                --}}
                @if($message['role'] != 'system')
                    <div class="message {{ $message['role'] }}">
                        @if($message['role'] == 'assistant')
                            {{-- Si habla la IA, le asignamos el nombre del paciente --}}
                            <span class="author">{{ $patient['name'] }}</span>
                        @endif
                        {{ $message['content'] }}
                    </div>
                @endif
            @endforeach

            {{--
            Indicador de "Escribiendo...":
            Controlado por JS (showLoading/hideLoading) para mejorar la
            sensación de respuesta del sistema.
            --}}
            <div id="loadingIndicator" class="message assistant" style="display: none;">
                <span class="author">{{ $patient['name'] }}</span>
                <span class="typing-dots">Escribiendo...</span>
            </div>
        </div>

        {{-- Área de Input: Entrada de texto del estudiante --}}
        <div class="chat-input-area">
            <input type="text" class="chat-input" id="userMessage" placeholder="Escribe tu pregunta al paciente..."
                autocomplete="off">
            <button class="btn-send" id="btnSend">
                {{-- Icono de avión de papel (Enviar) --}}
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                    stroke-linecap="round" stroke-linejoin="round">
                    <line x1="22" y1="2" x2="11" y2="13"></line>
                    <polygon points="22 2 15 22 11 13 2 9 22 2"></polygon>
                </svg>
            </button>
        </div>
    </div>

    <x-slot:scripts>
        {{--
        PUENTE LARAVEL -> JAVASCRIPT:
        Esta técnica es excelente. Pasamos rutas, tokens y nombres de PHP a una
        constante global de JS para que el archivo 'chat.js' sea totalmente
        independiente y reutilizable.
        --}}
        <script>
            const CHAT_CONFIG = {
                sendUrl: "{{ route('simulation.send') }}",
                csrfToken: document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                patientName: "{{ $patient['name'] }}"
            };
        </script>
        {{-- Cargamos la lógica de comunicación que comentamos anteriormente --}}
        <script src="{{ asset('js/chat.js') }}"></script>
    </x-slot:scripts>
</x-layouts.app>