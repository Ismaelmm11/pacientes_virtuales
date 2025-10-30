<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Prueba de Despliegue</title>
    
    <link href="{{ asset('css/style.css') }}" rel="stylesheet">
    
    <style>
        #testArea {
            background: #34495e;
            padding: 20px;
            border-radius: 8px;
            margin-top: 20px;
        }
        #helloButton {
            background: #3498db;
            color: white;
            border: none;
            padding: 10px 15px;
            font-size: 16px;
            cursor: pointer;
            border-radius: 5px;
        }
        #messageArea {
            margin-top: 15px;
            font-size: 18px;
            font-weight: bold;
        }
    </style>
</head>
<body>

    <h1>¡Mi Proyecto Laravel Funciona!</h1>

    <div id="testArea">
        <button id="helloButton">Saludar desde PHP</button>
        <p id="messageArea"></p>
    </div>

    <script src="{{ asset('js/main.js') }}"></script>

</body>
</html>