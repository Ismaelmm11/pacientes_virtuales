<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pacientes Virtuales</title>
    <link href="{{ asset('css/style.css') }}" rel="stylesheet">
    
    <style>
        .main-container {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }

        .welcome-header {
            text-align: center;
            margin-bottom: 50px;
        }

        .welcome-header h1 {
            color: var(--color-primary-darker);
            font-size: 2.8rem;
            margin-bottom: 10px;
        }

        .welcome-header p {
            color: #666;
            font-size: 1.1rem;
        }

        /* Grid de opciones principales */
        .options-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 400px));
            gap: 40px;
            justify-content: center;
            max-width: 900px;
        }

        .option-card {
            background: white;
            border-radius: 20px;
            padding: 50px 40px;
            text-align: center;
            box-shadow: 0 8px 30px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
            text-decoration: none;
            border: 3px solid transparent;
        }

        .option-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 15px 40px rgba(0,0,0,0.15);
        }

        .option-card.card-create {
            border-color: #9B59B6;
        }

        .option-card.card-create:hover {
            border-color: #8E44AD;
            background: linear-gradient(135deg, #fff 0%, #F5EEF8 100%);
        }

        .option-card.card-consult {
            border-color: #3498DB;
        }

        .option-card.card-consult:hover {
            border-color: #2980B9;
            background: linear-gradient(135deg, #fff 0%, #EBF5FB 100%);
        }

        .option-icon {
            width: 80px;
            height: 80px;
            margin: 0 auto 25px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .card-create .option-icon {
            background: linear-gradient(135deg, #9B59B6, #8E44AD);
        }

        .card-consult .option-icon {
            background: linear-gradient(135deg, #3498DB, #2980B9);
        }

        .option-icon svg {
            width: 40px;
            height: 40px;
            color: white;
        }

        .option-card h2 {
            font-size: 1.6rem;
            margin-bottom: 15px;
        }

        .card-create h2 {
            color: #8E44AD;
        }

        .card-consult h2 {
            color: #2980B9;
        }

        .option-card p {
            color: #666;
            font-size: 1rem;
            line-height: 1.5;
        }

        /* Botón Logout */
        .logout-container {
            position: fixed;
            top: 20px;
            right: 20px;
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

        /* Vista para no logueados */
        .guest-buttons {
            display: flex;
            gap: 15px;
            margin-top: 30px;
            flex-wrap: wrap;
            justify-content: center;
        }

        .btn-guest {
            padding: 12px 30px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.2s;
        }

        .btn-login {
            background: #3498db;
            color: white;
        }

        .btn-login:hover {
            background: #2980b9;
        }

        .btn-register {
            background: #2ecc71;
            color: white;
        }

        .btn-register:hover {
            background: #27ae60;
        }
    </style>
</head>
<body>

    @auth
    <div class="logout-container">
        <form action="{{ route('logout') }}" method="POST">
            @csrf
            <button type="submit" class="btn-logout">Cerrar Sesión</button>
        </form>
    </div>
    @endauth

    <div class="main-container">
        <div class="welcome-header">
            @auth
                <h1>Hola, {{ Auth::user()->first_name }} 👋</h1>
                <p>¿Qué te gustaría hacer hoy?</p>
            @else
                <h1>Bienvenido a Pacientes Virtuales</h1>
                <p>Plataforma de simulación clínica con inteligencia artificial</p>
                <div class="guest-buttons">
                    <a href="{{ route('login') }}" class="btn-guest btn-login">Iniciar Sesión</a>
                    <a href="{{ route('register.start') }}" class="btn-guest btn-register">Registrarse</a>
                </div>
            @endauth
        </div>

        @auth
        <div class="options-grid">
            
            <a href="{{ route('patients.create') }}" class="option-card card-create">
                <div class="option-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/>
                        <circle cx="9" cy="7" r="4"/>
                        <line x1="19" y1="8" x2="19" y2="14"/>
                        <line x1="16" y1="11" x2="22" y2="11"/>
                    </svg>
                </div>
                <h2>Crear Pacientes</h2>
                <p>Diseña casos clínicos complejos con personalidad, historial médico y comportamientos únicos.</p>
            </a>

            <a href="{{ route('consultation.dashboard') }}" class="option-card card-consult">
                <div class="option-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>
                    </svg>
                </div>
                <h2>Consultas</h2>
                <p>Inicia una simulación de consulta médica con pacientes virtuales impulsados por IA.</p>
            </a>

        </div>
        @endauth
    </div>

</body>
</html>