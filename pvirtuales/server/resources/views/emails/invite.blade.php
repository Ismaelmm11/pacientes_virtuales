<!DOCTYPE html>
<html>
<head>
    <title>Completa tu registro</title>
</head>
<body>
    <h1>¡Ya casi estás!</h1>
    <p>Gracias por registrarte en Pacientes Virtuales. Por favor, haz clic en el enlace de abajo para completar tu registro.</p>
    
    <!-- 
      route('register.form', $token) crea la URL completa, 
      ej: http://tudominio.com/registrar/asdf1234...
    -->
    <a href="{{ route('register.form', $token) }}">
        Completar mi registro
    </a>

    <p>Si no has solicitado este registro, por favor ignora este email.</p>
    <p>Este enlace caducará en 24 horas.</p>
</body>
</html>
