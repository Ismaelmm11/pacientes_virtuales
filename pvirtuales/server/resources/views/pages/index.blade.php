<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pacientes Virtuales - Inicio</title>
    
    <link href="{{ asset('css/style.css') }}" rel="stylesheet">
    
    <style>
        /* Estilos específicos para la página de inicio */
        .home-wrapper {
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 40px 20px;
            text-align: center;
            width: 100%;
        }

        .home-header {
            margin-bottom: 40px;
        }

        .home-header h1 {
            font-size: 52px;
            margin-bottom: 20px;
            color: var(--color-primary-darker);
            font-weight: 700;
            line-height: 1.2;
        }

        .home-header p {
            font-size: 20px;
            color: var(--color-text-muted);
            margin-bottom: 0;
        }

        .quick-links {
            display: flex;
            gap: 20px;
            margin-bottom: 50px;
            flex-wrap: wrap;
            justify-content: center;
            width: 100%;
            max-width: 500px;
        }

        .quick-link {
            background: white;
            color: var(--color-primary-darker);
            padding: 16px 32px;
            border-radius: var(--border-radius);
            text-decoration: none;
            font-weight: 600;
            font-size: 16px;
            transition: var(--transition);
            box-shadow: var(--shadow-sm);
            flex: 1;
            min-width: 180px;
        }

        .quick-link:hover {
            background: linear-gradient(135deg, var(--color-primary) 0%, var(--color-primary-dark) 100%);
            color: white;
            transform: translateY(-3px);
            box-shadow: var(--shadow-md);
        }

        #testArea {
            background: white;
            padding: 40px;
            border-radius: var(--border-radius);
            max-width: 500px;
            width: 100%;
            box-shadow: var(--shadow-lg);
        }

        #testArea h3 {
            color: var(--color-primary-darker);
            margin-bottom: 20px;
            font-size: 20px;
        }

        #helloButton {
            background: linear-gradient(135deg, var(--color-primary) 0%, var(--color-primary-dark) 100%);
            color: white;
            border: none;
            padding: 16px 28px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            border-radius: var(--border-radius);
            transition: var(--transition);
            width: 100%;
            box-shadow: var(--shadow-sm);
        }

        #helloButton:hover {
            background: linear-gradient(135deg, var(--color-primary-dark) 0%, var(--color-primary-darker) 100%);
            transform: translateY(-3px);
            box-shadow: var(--shadow-md);
        }

        #messageArea {
            margin-top: 25px;
            font-size: 18px;
            font-weight: 600;
            min-height: 30px;
            color: var(--color-success);
            display: block;
        }

        @media (max-width: 576px) {
            .home-header h1 {
                font-size: 36px;
            }
            
            .home-header p {
                font-size: 16px;
            }
            
            .quick-links {
                flex-direction: column;
                gap: 15px;
                margin-bottom: 40px;
            }
            
            .quick-link {
                width: 100%;
                min-width: unset;
            }

            #testArea {
                padding: 30px 25px;
            }
        }
    </style>
</head>
<body>
    <div class="home-wrapper">
        <div class="home-header">
            <h1>🏥 Pacientes Virtuales</h1>
            <p>Sistema de práctica médica con asistentes de IA</p>
        </div>

        <div class="quick-links">
            <a href="{{ route('register.start') }}" class="quick-link">
                📝 Registrarse
            </a>
            <a href="#" class="quick-link">
                🔐 Iniciar Sesión
            </a>
        </div>

        <div id="testArea">
            <h3>Prueba de Funcionalidad</h3>
            <button id="helloButton">Saludar desde PHP</button>
            <p id="messageArea"></p>
        </div>
    </div>

    <script src="{{ asset('js/main.js') }}"></script>
</body>
</html>