# Modelos Base de Datos

Este directorio contiene los modelos de Eloquent ORM. Cada clase actúa como una capa de abstracción sobre las tablas de la base de datos, 
gestionando la integridad de los datos y las relaciones del sistema.

---

## Modelos Principales

1. **User.php**

- Gestiona la entidad principal de autenticación.
- Roles: Incluye métodos helper como isAdmin(), isTeacher() y isStudent() para facilitar la lógica de permisos en toda la app.
- Casts: Automatiza la conversión de fechas (birth_date) y el hasheo de contraseñas.
- Accessors: Define el atributo virtual full_name para simplificar la visualización de nombres.

2. **Role.php**

- Define los tipos de usuario del sistema.
- Constantes: Centraliza los IDs fijos (STUDENT_ID, TEACHER_ID, ADMIN_ID) para evitar el uso de "números mágicos" en el código.
- Relaciones: Gestiona la relación uno-a-muchos con los usuarios.

3. **UserInvitation.php**

- Almacena los tokens temporales para el registro de nuevos usuarios mediante invitación.

---

## Configuración de los modelos:

- Inmutabilidad de Tiempos: Se ha deshabilitado el campo updated_at que viene por defecto en Laravel en tablas donde no es 
necesario, optimizando el rendimiento y la estructura de la DB.
- Asignación Masiva: Uso estricto de $fillable para prevenir vulnerabilidades de seguridad.
- Clean Code: Separación clara entre lógica de persistencia (Eloquent), relaciones y métodos auxiliares.