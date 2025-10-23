# Directorio `Controllers`

Este directorio contiene los **Controladores**.

Su **única responsabilidad** es actuar como intermediario entre las peticiones HTTP (rutas) y la lógica de negocio (servicios). Un controlador debe:
1. Recibir la petición (`Request`).
2. Validar los datos de entrada.
3. Llamar al servicio correspondiente para ejecutar la acción.
4. Devolver una respuesta (`Response`), generalmente en formato JSON.

**No debe contener lógica de negocio ni consultas a la base de datos.**