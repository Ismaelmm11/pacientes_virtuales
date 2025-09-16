# Directorio `Middleware`

Este directorio contiene los **Middleware**.

Un middleware es una capa de código que se ejecuta **antes** o **después** de un controlador. Se utiliza para filtrar y procesar las peticiones HTTP de forma centralizada.

**Casos de uso comunes:**
- `AuthMiddleware`: Verificar que un usuario esté autenticado.
- `AdminMiddleware`: Comprobar si el usuario tiene permisos de administrador.
- `LogMiddleware`: Registrar información sobre cada petición entrante.