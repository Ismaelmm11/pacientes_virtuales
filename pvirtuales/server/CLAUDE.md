# Pacientes Virtuales — Guía de Proyecto

## Descripción
Plataforma web de educación médica donde los profesores crean pacientes virtuales impulsados por IA y los estudiantes practican entrevistas clínicas con ellos. El alumno conversa en tiempo real con un paciente simulado por IA y después completa un cuestionario evaluativo.

**Estado actual:** MVP funcional en desarrollo activo (~65-70% completado).

## Stack Técnico
- **Framework:** Laravel 12, PHP 8.2+
- **Base de datos:** MySQL (`cacientes_virtuales`, host: 127.0.0.1:3306, user: root, sin contraseña)
- **Frontend:** Blade (SSR), CSS custom con variables CSS (sin Bootstrap/Tailwind), Vanilla JS
- **Build tool:** Vite 7 + laravel-vite-plugin (Tailwind está instalado pero NO se usa — CSS es 100% custom)
- **Icons:** Lucide Icons (CDN)
- **Email:** SMTP Gmail con App Password, driver de cola: database
- **Deploy:** OVH hosting, subdirectorio `/pacientes/`, CI/CD via GitHub Actions (FTP deploy)
- **IA:** 5 proveedores via Factory pattern + `AIServiceInterface` (sin SDKs oficiales, todo via `Http::` de Laravel)

## Proveedores de IA

| Proveedor | Modelos | Defecto |
|-----------|---------|---------|
| OpenAI | gpt-4o, gpt-4o-mini, gpt-4-turbo | gpt-4o-mini |
| Anthropic | claude-opus-4-5, claude-sonnet-4-5, claude-haiku-4-5 | claude-haiku-4-5-20251001 |
| Google | gemini-3-flash, gemini-2.5-pro | gemini-3-flash |
| xAI | grok-2-latest, grok-beta | grok-2-latest |
| Mistral | mistral-large-latest, mistral-small-latest | mistral-small-latest |

Temperatura: **0.7** hardcoded. `max_tokens`: 1024 hardcoded en ClaudeService y MistralService.

## Arquitectura y Patrones

**Monolito MVC con Service Layer:**
- **Controllers** → orquestan HTTP request/response
- **FormRequests** → validación centralizada
- **Services** (`PatientService`, `PromptGeneratorService`) → lógica de negocio
- **Models Eloquent** → acceso a datos con relaciones ORM
- **AI Services (Factory Pattern)** → `AIFactory::create($providerKey)` → implementaciones de `AIServiceInterface`

### Roles de Usuario
Roles fijos en tabla `roles`, referenciados por `users.role_id`:
- **1 = Student** (Alumno)
- **2 = Teacher** (Profesor)
- **3 = Admin**

Middleware: `EnsureIsTeacher` permite role_id 2 y 3. `EnsureIsStudent` permite solo role_id 1.

### Roles en Asignaturas (pivot `subject_user`)
Independiente de los roles globales:
- `role = 'student'` — alumno matriculado
- `role = 'collaborator'` — profesor colaborador (puede ver/usar pero no creó la asignatura)

## Convenciones de Código

### Idioma
- **Código** (variables, métodos, clases, migraciones): en inglés
- **Interfaz de usuario** (textos, labels, mensajes): en español
- **Comentarios**: en español

### CSS
- `style.css`: estilos globales
- `dashboard.css`: tablas, stats, cards
- `create-patient.css`: formulario de creación de pacientes, prefijo `cp-`
- **NUNCA** usar atributos `style=""` inline — siempre clases CSS
- `overflow: hidden` rompe tooltips — usar `border-radius` en elementos hijos en su lugar
- En total hay ~11 archivos CSS en `public/css/`

### Emails
- No usar CSS externo ni `box-shadow` (incompatible con clientes de email)
- Usar `border-bottom` grueso con color turquesa `rgba(91, 231, 196, 0.3)` para simular profundidad
- Layout base en `resources/views/components/emails/layouts/base.blade.php`
- **NUNCA** usar `$subject` como propiedad en clases Mail (colisiona con Mailable) — usar `$asignatura`

## Base de Datos — Tablas principales

| Tabla | Descripción |
|-------|-------------|
| `users` | Sin `updated_at`. Campo `auth_at` para origen de registro |
| `roles` | 1=Student, 2=Teacher, 3=Admin |
| `subjects` | Asignaturas. Sin `updated_at` |
| `subject_user` | Pivot users↔subjects con campo `role` (student/collaborator) |
| `patients` | Cabecera del paciente. `mode` = 'basic' o 'advanced' |
| `patient_role_identity` | Identidad: nombre, edad, género, rol, contexto sociolaboral |
| `patient_psychology` | Estado emocional, reglas de interacción, conflicto interno (JSON) |
| `patient_knowledge_base` | Síntomas, diagnóstico real, medicación, antecedentes, vicios (JSON) |
| `patient_conversation_logic` | Gatillos emocionales, contradicciones, frases límite, cierre (JSON) |
| `patient_prompts` | Prompt Markdown generado. Versionado, con hash para detectar cambios |
| `coherence_examples` | Pares pregunta/respuesta-correcta/incorrecta para guiar coherencia de la IA |
| `extra_information` | Archivos o texto adicional adjunto al caso |
| `questions` | Preguntas del cuestionario (MULTIPLE_CHOICE / TRUE_FALSE / OPEN_ENDED) |
| `test_attempts` | Intento de simulación + test por alumno. Guarda `interview_transcript` en JSON |
| `answers` | Respuestas del alumno al cuestionario. `is_correct` = null para OPEN_ENDED |
| `user_invitations` | Tokens de invitación (60 chars, 24h expiración) |

### Decisiones clave de BD
- Solo existen **4 migraciones** de las ~15 tablas. El resto se creó sin migración.
- `patients.subject_id` → FK a `subjects` (**actualmente hardcodeado a 1** en PatientService)
- `test_attempts.interview_transcript` → JSON con historial completo de la conversación
- `answers.created_at` añadido a la tabla
- `coherence_examples` es tabla separada — **NO** existe `ejemplo_coherencia` en `patient_conversation_logic`
- Tests y preguntas: todos los puntos valen igual → `100 / question_count` (campo `points` reservado para futuro)
- `randomize_questions` controla aleatorización de apariencia, no de orden (siempre aleatorio internamente)
- Las preguntas requeridas (`is_required`) cuentan dentro del límite `questions_per_test`

## Módulo de Simulación IA

**Flujo de contexto:**
1. `SimulationController::start()` construye historial inicial:
   - `role: system` → `patient_prompts.prompt_content`
   - `role: assistant` → `patient_knowledge_base.frase_inicial`
2. Guarda en sesión de Laravel (filesystem)
3. Cada `sendMessage()` recupera historial completo, añade el mensaje del usuario, envía TODO a la IA, añade la respuesta y persiste en sesión

El historial crece sin límite (sin resúmenes ni truncado — riesgo con modelos pequeños de 1024 tokens).

**Generación del prompt:** `PromptGeneratorService` genera Markdown en 4 secciones:
1. **INSTRUCCIONES MAESTRAS** — misión, regla de oro, creatividad, verbosidad
2. **PERFIL DEL PERSONAJE** — identidad, psicología, emociones
3. **BASE DE CONOCIMIENTO** — síntomas clasificados en 5 tipos de revelación (espontáneo / con pregunta / exagerado / oculto / mentira), antecedentes, medicación, diagnóstico sellado
4. **LÓGICA DE CONVERSACIÓN** — gatillos, contradicciones intencionales, cierre, instrucciones del profesor

El prompt se regenera y se guarda en `patient_prompts` al publicar el paciente.

## Bugs Conocidos

1. **`subject_id` hardcodeado a `1`** en `PatientService` — todos los pacientes van a la asignatura 1
2. **Relación `coherenceExamples()` puede no existir** en el modelo `Patient` — `PromptGeneratorService` la usa en línea 113
3. **`PatientType` importado pero no existe** — importación rota en algún archivo
4. **`StorePatientRequest`** le faltan validaciones para varios campos
5. **`QuestionController::updateConfig()`** no completamente implementado
6. **`test-take.blade.php`** no implementada — vista del alumno para hacer el test
7. **`UserFactory`** usa campo `name` en vez de `first_name`/`last_name` — el seeder por defecto falla
8. **Chat sin límite de tokens** — historial crece indefinidamente; riesgo de superar contexto en modelos pequeños
9. **Historial en sesión** — si el alumno abre dos simulaciones en paralelo (dos tabs), se sobreescriben
10. **Typo en `PromptGeneratorService` línea 313** — `"**información completa**s"` (la `s` queda fuera del bold y aparece en el prompt enviado a la IA)

## Estado de Implementación

| Módulo | Estado |
|--------|--------|
| Auth (login / invitación / registro) | ✅ ~90% |
| Panel profesor | ✅ ~85% |
| Creación de pacientes (básico) | ✅ ~80% |
| Creación de pacientes (avanzado) | ✅ ~75% |
| Simulación IA (chat) | ✅ ~85% |
| Cuestionarios evaluativos | 🔶 ~60% |
| Panel estudiante | 🔶 ~55% |
| Seguimiento / calificación profesor | 🔶 ~50% |
| Admin | ❌ ~10% |
| Tests automatizados (PHPUnit) | ❌ ~0% |

## Pendiente de Implementar
- Vista `test-take.blade.php` para que alumnos hagan el test
- Completar `QuestionController::submit()` (lógica incompleta)
- Flujos de corrección de tests (automático y manual)
- Dashboard del estudiante (vistas parcialmente implementadas)
- Vistas de seguimiento del profesor (lógica parcial)
- Panel de administración
- Recuperación de contraseña
- Escribir las migraciones faltantes (~11 tablas sin migración)
- Paginación en listados
- Gestión de errores de API IA (actualmente sin manejo graceful)
- Límite / resumen de historial para conversaciones largas
- Rate limiting en `/simulacion/enviar`

## Instrucciones para Claude Code
- **Nunca hacer cambios en el código sin explicar primero qué se va a cambiar y por qué.** Describir archivo, línea y efecto antes de editar.
- Cuando propongas cambios, explica el razonamiento antes de mostrar código
- Respeta siempre las convenciones de idioma, CSS y arquitectura descritas arriba
- Si necesitas ver un archivo para responder con precisión, léelo primero en lugar de asumir su contenido
- Prefiere cambios quirúrgicos y específicos sobre reescrituras completas
