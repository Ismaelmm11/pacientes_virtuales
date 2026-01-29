<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear Pacientes - Próximamente</title>
    <link href="{{ asset('css/style.css') }}" rel="stylesheet">
    
    <style>
        .coming-soon-container {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            padding: 20px;
            text-align: center;
        }

        .coming-soon-icon {
            width: 120px;
            height: 120px;
            background: linear-gradient(135deg, #9B59B6, #8E44AD);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 40px;
            animation: pulse 2s ease-in-out infinite;
        }

        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.05); }
        }

        .coming-soon-icon svg {
            width: 60px;
            height: 60px;
            color: white;
        }

        .coming-soon-container h1 {
            font-size: 3rem;
            color: #8E44AD;
            margin-bottom: 15px;
        }

        .coming-soon-container .subtitle {
            font-size: 1.3rem;
            color: #666;
            margin-bottom: 30px;
        }

        .coming-soon-container .description {
            max-width: 500px;
            color: #888;
            line-height: 1.6;
            margin-bottom: 40px;
        }

        .feature-preview {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            justify-content: center;
            max-width: 700px;
            margin-bottom: 50px;
        }

        .feature-item {
            background: white;
            padding: 20px 25px;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.08);
            display: flex;
            align-items: center;
            gap: 12px;
            color: #555;
        }

        .feature-item svg {
            width: 24px;
            height: 24px;
            color: #9B59B6;
        }

        .btn-back-home {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            padding: 14px 30px;
            background: linear-gradient(135deg, #9B59B6, #8E44AD);
            color: white;
            text-decoration: none;
            border-radius: 10px;
            font-weight: 600;
            transition: all 0.3s;
        }

        .btn-back-home:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(142, 68, 173, 0.3);
        }

        .btn-back-home svg {
            width: 20px;
            height: 20px;
        }

        /* Navegación superior */
        .top-nav {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 30px;
            background: white;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }

        .btn-nav {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 8px 16px;
            background: #f8f9fa;
            border: 1px solid #e9ecef;
            border-radius: 8px;
            text-decoration: none;
            color: #333;
            font-weight: 500;
            transition: all 0.2s;
        }

        .btn-nav:hover {
            background: #e9ecef;
        }

        .btn-nav svg {
            width: 18px;
            height: 18px;
        }

        .btn-logout {
            background: #ff4757;
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 20px;
            cursor: pointer;
            font-weight: bold;
            transition: background 0.2s;
        }

        .btn-logout:hover {
            background: #e84141;
        }
    </style>
</head>
<body>

    <nav class="top-nav">
        <a href="{{ route('home') }}" class="btn-nav">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <line x1="19" y1="12" x2="5" y2="12"/>
                <polyline points="12 19 5 12 12 5"/>
            </svg>
            Volver al Inicio
        </a>
        <form action="{{ route('logout') }}" method="POST">
            @csrf
            <button type="submit" class="btn-logout">Cerrar Sesión</button>
        </form>
    </nav>

    <div class="coming-soon-container">
        
        <div class="coming-soon-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/>
                <circle cx="9" cy="7" r="4"/>
                <line x1="19" y1="8" x2="19" y2="14"/>
                <line x1="16" y1="11" x2="22" y2="11"/>
            </svg>
        </div>

        <h1>Próximamente</h1>
        <p class="subtitle">Creador de Pacientes Virtuales</p>
        
        <p class="description">
            Estamos trabajando en una herramienta que te permitirá diseñar 
            pacientes virtuales personalizados con casos clínicos complejos, 
            sin necesidad de conocimientos técnicos.
        </p>

        <div class="feature-preview">
            <div class="feature-item">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                    <circle cx="12" cy="7" r="4"/>
                </svg>
                <span>Personalidad y comportamiento</span>
            </div>
            <div class="feature-item">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                    <polyline points="14 2 14 8 20 8"/>
                </svg>
                <span>Historial médico completo</span>
            </div>
            <div class="feature-item">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="10"/>
                    <path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/>
                    <line x1="12" y1="17" x2="12.01" y2="17"/>
                </svg>
                <span>Lógica de revelación</span>
            </div>
            <div class="feature-item">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>
                </svg>
                <span>Diálogos contextuales</span>
            </div>
        </div>

        <a href="{{ route('home') }}" class="btn-back-home">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/>
                <polyline points="9 22 9 12 15 12 15 22"/>
            </svg>
            Volver al Inicio
        </a>

    </div>

</body>
</html>