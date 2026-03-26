# Pacientes Virtuales — Guía de Proyecto

## Descripción
Plataforma de educación médica en Laravel donde los profesores crean pacientes virtuales potenciados por IA y los estudiantes practican entrevistas clínicas mediante consultas simuladas.

## Stack Técnico
- **Framework:** Laravel (Blade components, service layer pattern, FormRequest validation)
- **Base de datos:** MySQL
- **CSS:** Custom, sin frameworks. Sin Vite ni build tools — assets estáticos servidos desde /public
- **Deploy:** OVH hosting, subdirectorio `/pacientes/`, CI/CD via GitHub Actions
- **IA:** 5 proveedores integrados via Factory pattern (OpenAI, Anthropic, Google Gemini, xAI Grok, Mistral)

## Arquitectura y Patrones

### Service Layer
Los controladores delegan la lógica de negocio a clases de servicio (`PatientService`, `PromptGeneratorService`). La validación se hace con clases FormRequest.

### Roles de Usuario
Roles fijos almacenados como constantes en el modelo User:
- 1 = Admin
- 2 = Teacher (Profesor)
- 3 = Student (Estudiante)
El rol se determina por el campo `role_id` en la tabla users, no por pivot tables.

### Proveedores de IA
Integrados via Factory pattern con `AIServiceInterface`. Cada proveedor implementa la interfaz.

## Convenciones de Código

### Idioma
- **Código** (variables, métodos, clases, migraciones): en inglés
- **Interfaz de usuario** (textos, labels, mensajes): en español
- **Comentarios**: en español

### CSS
- Archivo `style.css`: estilos globales
- Archivo `dashboard.css`: tablas, stats, cards
- Archivo `create-patient.css`: formulario de creación de pacientes, prefijo `cp-`
- **NUNCA** usar atributos `style=""` inline — siempre clases CSS
- `overflow: hidden` rompe tooltips — usar `border-radius` en elementos hijos en su lugar

### Emails
- No usar CSS externo ni `box-shadow` (incompatible con clientes de email)
- Usar `border-bottom` grueso con color turquesa `rgba(91, 231, 196, 0.3)` para simular profundidad
- Layout base en `resources/views/components/emails/layouts/base.blade.php`
- **NUNCA** usar `$subject` como propiedad en clases Mail (colisiona con Mailable) — usar `$asignatura`

## Estructura de Base de Datos (decisiones clave)

### Tests y Preguntas
- Todos los puntos valen igual: `100 / question_count` (campo `points` reservado para futuro)
- `randomize_questions` controla la aleatorización de apariencia, no de orden (siempre aleatorio)
- Las preguntas requeridas (`is_required`) cuentan dentro del límite `questions_per_test`
- Validación: non-required questions deben ser > `questions_per_test - required_count`

### Relaciones y FK
- Políticas ON DELETE definidas en 3 FKs
- `created_at` añadido a tabla `answers`
- `ejemplo_coherencia` eliminado de `patient_conversation_logic` — existe tabla dedicada `coherence_examples`
- El modelo Patient necesita relación `coherenceExamples()`

## Bugs Conocidos (pendientes de fix)
- `subject_id` hardcodeado a `1` en `PatientService` y ausente del formulario
- `ejemplo_coherencia` se guarda incorrectamente en `patient_conversation_logic`
- `PatientType` importado pero no existe
- `PromptGeneratorService` lee `$logic->ejemplo_coherencia` que no existe
- `StorePatientRequest` le faltan validaciones para varios campos

## Pendiente de Implementar
- Método `updateConfig()` en controlador y su ruta
- Vista `test-take.blade.php` para estudiantes
- Interfaz de edición de pacientes
- Flujos de corrección de tests (automático y manual)
- Dashboard del estudiante
- Vistas de seguimiento del profesor
- Panel de administración

## Instrucciones para Claude Code
- Cuando propongas cambios, explica el razonamiento antes de mostrar código
- Respeta siempre las convenciones de idioma, CSS y arquitectura descritas arriba
- Si necesitas ver un archivo para responder con precisión, léelo primero en lugar de asumir su contenido
- Prefiere cambios quirúrgicos y específicos sobre reescrituras completas