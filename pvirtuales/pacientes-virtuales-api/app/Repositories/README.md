# Directorio `Repositories`

Este directorio contiene los **Repositorios**.

Son la **capa de abstracción de acceso a datos**. Encapsulan toda la lógica para consultar la base de datos. Los servicios utilizan los repositorios para obtener y guardar datos sin saber cómo se hace.

**Beneficios:**
- **Desacoplamiento**: La lógica de negocio no sabe si los datos vienen de MySQL, una API externa o un archivo.
- **Testeabilidad**: Permite simular (mock) la base de datos en las pruebas unitarias.
- **Reutilización**: Las consultas comunes se centralizan en un único lugar.

Ejemplo: `UserRepository.php` con métodos como `findById()`, `findByEmail()`, `save(User $user)`.