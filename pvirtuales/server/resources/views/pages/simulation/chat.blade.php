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
                <span>{{ $patient['patient_description'] }}</span>
            </div>
            <div class="chat-header-right">
                <span class="ai-badge">IA EN USO: {{ $patient['isTeacher'] ? 'Sí' : 'No' }}
                    {{ strtoupper($aiModel) }}</span>
                @if($patient['isTeacher'])
                    <a href="{{ route('teacher.patients.preview', $patient['id']) }}" class="btn-exit">
                        Volver atrás
                    </a>
                @endif
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

    @if(!$patient['isTeacher'])
        <div class="chat-footer-student" id="chatFooterStudent">
            <button class="btn-finish" id="btnFinish" onclick="openFarewellModal()">
                <i data-lucide="phone-off"></i>
                Finalizar Consulta
            </button>
            <a href="{{ route('patients.test.take', $patient['id']) }}" class="btn-go-test" id="btnGoTest">
                <i data-lucide="clipboard-list"></i>
                Ir al Cuestionario
            </a>
        </div>
    @endif

    @if(!$patient['isTeacher'])
        <div class="farewell-overlay" id="farewellOverlay">
            <div class="farewell-modal">
                <div class="farewell-modal-header">
                    <h3>Finalizar Consulta</h3>
                    <p>Escribe tu mensaje de despedida al paciente. Una vez enviado, no podrás enviar más mensajes.</p>
                </div>
                <textarea class="farewell-input" id="farewellInput"
                    placeholder="Ej: Muchas gracias por venir, le recetaré algo para el dolor..."></textarea>
                <div class="farewell-modal-actions">
                    <button class="btn-farewell-cancel" onclick="closeFarewellModal()">Cancelar</button>
                    <button class="btn-farewell-send" id="btnFarewellSend" onclick="sendFarewell()">
                        Enviar y Finalizar
                    </button>
                </div>
            </div>
        </div>
    @endif

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
                patientName: "{{ $patient['name'] }}",
                isTeacher: {{ $patient['isTeacher'] ? 'true' : 'false' }},
                testUrl: "{{ route('patients.test.take', $patient['id']) }}"
            };
        </script>
        {{-- Cargamos la lógica de comunicación que comentamos anteriormente --}}
        <script src="{{ asset('js/chat.js') }}"></script>
    </x-slot:scripts>
</x-layouts.app>