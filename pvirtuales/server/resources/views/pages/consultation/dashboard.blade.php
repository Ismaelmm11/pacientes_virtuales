<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pacientes Virtuales - Dashboard</title>
    <link href="{{ asset('css/style.css') }}" rel="stylesheet">
    
    <style>
        /* Estilos Específicos del Dashboard */
        .dashboard-header {
            text-align: center;
            margin-bottom: 40px;
            margin-top: 30px;
        }
        .dashboard-header h1 {
            color: var(--color-primary-darker);
            font-size: 2.5rem;
            margin-bottom: 10px;
        }

        /* --- GRID DE LAS 4 IAs --- */
        .ai-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 30px;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }

        /* Tarjeta Base */
        .ai-card {
            background: white;
            border-radius: 16px;
            padding: 25px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
            transition: transform 0.3s ease;
            border-top: 6px solid #ccc; /* Color por defecto */
        }

        .ai-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 30px rgba(0,0,0,0.12);
        }

        .ai-card h2 {
            font-size: 1.8rem;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .ai-desc {
            color: #666;
            font-size: 0.95rem;
            margin-bottom: 25px;
            height: 40px; /* Altura fija para alinear botones */
        }

        /* Colores de Marca para cada IA */
        .card-openai { border-top-color: #10A37F; } /* Verde ChatGPT */
        .card-openai h2 { color: #10A37F; }

        .card-gemini { border-top-color: #4E86F6; } /* Azul Google */
        .card-gemini h2 { color: #4E86F6; }

        .card-claude { border-top-color: #D97757; } /* Naranja Anthropic */
        .card-claude h2 { color: #D97757; }

        .card-grok { border-top-color: #000000; } /* Negro X */
        .card-grok h2 { color: #000000; }

        .card-mistral { border-top-color: #FF7000; } /* Naranja Mistral */
        .card-mistral h2 { color: #FF7000; }

        /* --- BOTONES DE PACIENTES --- */
        .patient-buttons {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .btn-patient {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 12px 20px;
            background-color: #f8f9fa;
            border: 1px solid #e9ecef;
            border-radius: 8px;
            text-decoration: none;
            color: #333;
            font-weight: 600;
            transition: all 0.2s;
        }

        /* Hover específico por IA */
        .card-openai .btn-patient:hover { background: #E5F8F2; color: #0B755B; border-color: #10A37F; }
        .card-gemini .btn-patient:hover { background: #EEF4FF; color: #2C56AA; border-color: #4E86F6; }
        .card-claude .btn-patient:hover { background: #FDF2EE; color: #9C482F; border-color: #D97757; }
        .card-grok .btn-patient:hover   { background: #EEEEEE; color: #000000; border-color: #000000; }
        .card-mistral .btn-patient:hover { background: #FFF3E8; color: #CC5A00; border-color: #FF7000; }


        .status-badge {
            font-size: 0.75rem;
            padding: 4px 8px;
            border-radius: 12px;
            background: #ddd;
            color: #555;
        }
        
        /* Botón Logout flotante */
        .logout-container {
            position: fixed;
            top: 20px;
            right: 20px;
        }
    </style>
</head>
<body>

    @auth
    <div class="logout-container">
        <form action="{{ route('logout') }}" method="POST">
            @csrf
            <button type="submit" style="background: #ff4757; color: white; border: none; padding: 8px 16px; border-radius: 20px; cursor: pointer; font-weight: bold;">
                Cerrar Sesión
            </button>
        </form>
    </div>
    @endauth

    <div class="dashboard-header">
        @auth
            <h1>Hola, {{ Auth::user()->first_name }} 👋</h1>
            <p>Selecciona una IA y un caso clínico para comenzar</p>
        @else
            <h1>Bienvenido a Pacientes Virtuales</h1>
            <div style="margin-top: 20px;">
                <a href="{{ route('login') }}" class="quick-link" style="background: #3498db; color: white; padding: 10px 20px; border-radius: 8px; text-decoration: none;">Iniciar Sesión</a>
                <a href="{{ route('register.start') }}" class="quick-link" style="background: #2ecc71; color: white; padding: 10px 20px; border-radius: 8px; text-decoration: none;">Registrarse</a>
            </div>
        @endauth
    </div>

    @auth
    <div class="ai-grid">

        <div class="ai-card card-openai">
            <h2>
                <svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor"><path d="M12,2A10,10 0 0,1 22,12A10,10 0 0,1 12,22A10,10 0 0,1 2,12A10,10 0 0,1 12,2M12,4A8,8 0 0,0 4,12A8,8 0 0,0 12,20A8,8 0 0,0 20,12A8,8 0 0,0 12,4Z" /></svg>
                ChatGPT
            </h2>
            <p class="ai-desc">Equilibrado, rápido y con fuerte razonamiento lógico.</p>
            <div class="patient-buttons">
                <a href="{{ route('simulation.start', ['ai' => 'gpt', 'patient' => 1]) }}" class="btn-patient">
                    <span>JUANA</span>
                </a>
                <a href="{{ route('simulation.start', ['ai' => 'gpt', 'patient' => 2]) }}" class="btn-patient">
                    <span>ROBERTO</span>
                </a>
                <a href="{{ route('simulation.start', ['ai' => 'gpt', 'patient' => 3]) }}" class="btn-patient">
                    <span>ELENA</span> 
                </a>
                <a href="{{ route('simulation.start', ['ai' => 'gpt', 'patient' => 4]) }}" class="btn-patient">
                    <span>DANIEL</span> 
                </a>
            </div>
        </div>

        <div class="ai-card card-claude">
            <h2>
                <svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor"><path d="M12,2L2,22H22L12,2Z" /></svg>
                Claude
            </h2>
            <p class="ai-desc">El más humano, empático y detallista en el roleplay.</p>
            <div class="patient-buttons">
                <a href="{{ route('simulation.start', ['ai' => 'claude', 'patient' => 1]) }}" class="btn-patient">
                    <span>JUANA</span>
                </a>
                <a href="{{ route('simulation.start', ['ai' => 'claude', 'patient' => 2]) }}" class="btn-patient">
                    <span>ROBERTO</span>
                </a>
                <a href="{{ route('simulation.start', ['ai' => 'claude', 'patient' => 3]) }}" class="btn-patient">
                    <span>ELENA</span> 
                </a>
                <a href="{{ route('simulation.start', ['ai' => 'claude', 'patient' => 4]) }}" class="btn-patient">
                    <span>DANIEL</span> 
                </a>
            </div>
        </div>

        <div class="ai-card card-gemini">
            <h2>
                <svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor"><path d="M12,2L14.5,9L22,12L14.5,15L12,22L9.5,15L2,12L9.5,9L12,2Z" /></svg>
                Gemini
            </h2>
            <p class="ai-desc">Enorme memoria de contexto y acceso a datos médicos.</p>
            <div class="patient-buttons">
                <a href="{{ route('simulation.start', ['ai' => 'gemini', 'patient' => 1]) }}" class="btn-patient">
                    <span>JUANA</span>
                </a>
                <a href="{{ route('simulation.start', ['ai' => 'gemini', 'patient' => 2]) }}" class="btn-patient">
                    <span>ROBERTO</span>
                </a>
                <a href="{{ route('simulation.start', ['ai' => 'gemini', 'patient' => 3]) }}" class="btn-patient">
                    <span>ELENA</span> 
                </a>
                <a href="{{ route('simulation.start', ['ai' => 'gemini', 'patient' => 4]) }}" class="btn-patient">
                    <span>DANIEL</span> 
                </a>
            </div>
        </div>

        <div class="ai-card card-grok">
            <h2>
                <svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor"><path d="M2,2L22,22M22,2L2,22" stroke="currentColor" stroke-width="3" /></svg>
                Grok
            </h2>
            <p class="ai-desc">Sin filtros, ideal para pacientes difíciles u hostiles.</p>
            <div class="patient-buttons">
                <a href="{{ route('simulation.start', ['ai' => 'grok', 'patient' => 1]) }}" class="btn-patient">
                    <span>JUANA</span>
                </a>
                <a href="{{ route('simulation.start', ['ai' => 'grok', 'patient' => 2]) }}" class="btn-patient">
                    <span>ROBERTO</span>
                </a>
                <a href="{{ route('simulation.start', ['ai' => 'grok', 'patient' => 3]) }}" class="btn-patient">
                    <span>ELENA</span> 
                </a>
                <a href="{{ route('simulation.start', ['ai' => 'grok', 'patient' => 4]) }}" class="btn-patient">
                    <span>DANIEL</span> 
                </a>
            </div>
        </div>

        <div class="ai-card card-mistral">
            <h2>
                <svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
                    <rect x="2" y="4" width="4" height="4"/>
                    <rect x="10" y="4" width="4" height="4"/>
                    <rect x="18" y="4" width="4" height="4"/>
                    <rect x="2" y="10" width="4" height="4"/>
                    <rect x="6" y="10" width="4" height="4"/>
                    <rect x="10" y="10" width="4" height="4"/>
                    <rect x="14" y="10" width="4" height="4"/>
                    <rect x="18" y="10" width="4" height="4"/>
                    <rect x="2" y="16" width="4" height="4"/>
                    <rect x="18" y="16" width="4" height="4"/>
                </svg>
                Mistral
            </h2>
            <p class="ai-desc">IA francesa, eficiente y con excelente relación calidad-coste.</p>
            <div class="patient-buttons">
                <a href="{{ route('simulation.start', ['ai' => 'mistral', 'patient' => 1]) }}" class="btn-patient">
                    <span>JUANA</span>
                </a>
                <a href="{{ route('simulation.start', ['ai' => 'mistral', 'patient' => 2]) }}" class="btn-patient">
                    <span>ROBERTO</span>
                </a>
                <a href="{{ route('simulation.start', ['ai' => 'mistral', 'patient' => 3]) }}" class="btn-patient">
                    <span>ELENA</span> 
                </a>
                <a href="{{ route('simulation.start', ['ai' => 'mistral', 'patient' => 4]) }}" class="btn-patient">
                    <span>DANIEL</span> 
                </a>
            </div>
        </div>

    </div>
    @endauth

</body>
</html>