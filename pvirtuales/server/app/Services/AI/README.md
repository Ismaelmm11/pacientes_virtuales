# Módulo de Integración de IA (Laravel)

Este módulo proporciona una interfaz unificada para interactuar con múltiples proveedores de Inteligencia Artificial (OpenAI, Claude, Gemini, Grok y Mistral) utilizando patrones de diseño Factory.

---

## Arquitectura

-**AIServiceInterface**: Define el contrato estándar para todos los servicios de IA. Obliga a implementar el método sendMessage(array $history, float $temperature).
-**AIFactory**: Clase encargada de instanciar el driver correspondiente (gpt, claude, gemini, etc.) según la clave proporcionada.
-**Servicios (Drivers)**: Clases específicas que gestionan la lógica de autenticación, el formato de mensajes propio de cada API y la comunicación HTTP.

---

## Configuración

Archivo de Configuración: El archivo que se encuentra en *config/ai.php* centraliza la gestión de modelos y llaves de forma segura.

---

## Uso Básico
Para obtener una respuesta de cualquier IA, simplemente utiliza el Factory:

```
use App\Services\AI\AIFactory;

// 1. Crear el servicio deseado
$ai = AIFactory::create('claude'); // o 'gpt', 'gemini', etc.

// 2. Definir el historial (formato estándar)
$history = [
    ['role' => 'system', 'content' => 'Eres un asistente experto.'],
    ['role' => 'user', 'content' => '¿Cuál es la capital de Francia?']
];


// 3. Enviar mensaje
$respuesta = $ai->sendMessage($history, 0.7);

echo $respuesta;
```

---

## Extensibilidad

Para añadir un nuevo proveedor:

1. Crea una clase que implemente AIServiceInterface.
2. Registra el nuevo caso en el match de la clase AIFactory.
3. Añade las credenciales en config/ai.php.