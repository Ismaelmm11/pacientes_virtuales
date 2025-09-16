# Directorio `Services`

Este directorio contiene los **Servicios**, donde reside la **lógica de negocio principal**.

Un servicio orquesta las operaciones complejas, coordinando llamadas a diferentes repositorios y ejecutando las reglas de negocio.

**Ejemplo (`AuthService.php`):**
- Recibe los datos del `AuthController`.
- Llama al `UserRepository` para verificar si el usuario existe.
- Compara las contraseñas.
- Genera un token de autenticación (JWT).
- Devuelve el resultado al controlador.