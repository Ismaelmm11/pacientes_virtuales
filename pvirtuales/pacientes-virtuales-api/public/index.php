<?php

// Cargar el autoloader de Composer
require __DIR__ . '/../vendor/autoload.php';

// Definir una respuesta simple para la ruta raíz
echo json_encode([
    'status' => 'success',
    'message' => 'API de Pacientes Virtuales funcionando!'
]);