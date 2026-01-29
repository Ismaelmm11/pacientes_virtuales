# Guía de Desarrollo Laravel - Proyecto

## Stack Tecnológico
- Laravel (última versión)
- PHP 8.2+
- MySQL/PostgreSQL
- Blade Templates
- Vanilla JS / Alpine.js
- Tailwind CSS / Bootstrap

## Arquitectura del Proyecto

### Backend (PHP/Laravel)
```
app/
├── Http/
│   ├── Controllers/     # Lógica mínima
│   ├── Requests/        # Validaciones
│   └── Resources/       # Transformación de datos
├── Services/            # Lógica de negocio
├── Repositories/        # Acceso a datos (opcional)
├── Models/              # Eloquent models
└── Traits/              # Funcionalidad reutilizable
```

### Frontend
```
resources/
└── views/
    ├── components/      # Blade components
    ├── layouts/         # Layouts base
    └── pages/           # Vistas específicas

public/
├── css/                 # Archivos CSS compilados/personalizados
└── js/
    └── modules/         # JS modularizado
```

**IMPORTANTE**: Los archivos CSS y JS deben estar en `public/` para compatibilidad 
con el servidor de hosting. Referenciar en Blade con `asset()`:
```php
<link rel="stylesheet" href="{{ asset('css/app.css') }}">
<script src="{{ asset('js/app.js') }}"></script>
```

## Convenciones de Código

### Nomenclatura
- Controllers: `PascalCase` + sufijo `Controller` (ej: `UserController`)
- Models: `PascalCase`, singular (ej: `User`, `BlogPost`)
- Tablas: `snake_case`, plural (ej: `users`, `blog_posts`)
- Métodos: `camelCase` (ej: `getUserById()`)
- Variables: `camelCase` (ej: `$userName`)
- Constantes: `UPPER_SNAKE_CASE`
- Vistas: `kebab-case` (ej: `user-profile.blade.php`)
- Archivos CSS/JS: `kebab-case` (ej: `user-form.css`, `validation-module.js`)

### Patrones a Aplicar
1. **Service Pattern**: Lógica de negocio compleja
2. **Repository Pattern**: Si hay consultas complejas/reutilizables
3. **Form Request Validation**: Todas las validaciones
4. **Resource Controllers**: CRUD estándar
5. **API Resources**: Transformación de datos

### Ejemplo de Estructura por Feature
```
# Feature: Gestión de Usuarios

app/Http/Controllers/UserController.php
app/Http/Requests/StoreUserRequest.php
app/Services/UserService.php
app/Models/User.php
resources/views/users/index.blade.php
public/css/users.css (si es necesario CSS específico)
public/js/modules/user-handler.js
```

## Organización de Assets (CSS/JS)

### Estructura recomendada en public/
```
public/
├── css/
│   ├── app.css              # Estilos globales
│   ├── components/          # Estilos de componentes
│   └── pages/               # Estilos específicos de páginas
└── js/
    ├── app.js               # JavaScript global
    ├── modules/             # Módulos reutilizables
    │   ├── forms.js
    │   ├── validation.js
    │   └── ajax-handler.js
    └── pages/               # JS específico de páginas
```

### Buenas prácticas para Assets
- Minificar archivos en producción
- Versionado de assets con `mix()` o `asset()` + query string
- Cargar JS al final del body cuando sea posible
- CSS crítico en el head, resto puede ser diferido
- Usar defer/async en scripts cuando corresponda

## Principios de Código Limpio
- Single Responsibility Principle
- DRY (Don't Repeat Yourself)
- KISS (Keep It Simple, Stupid)
- Métodos pequeños (<20 líneas)
- Máximo 4 parámetros por método
- Comentarios en español para explicar el "por qué"
- Code in English, Comments in Spanish

## Seguridad
- CSRF protection en todos los forms
- Validación exhaustiva de inputs
- SQL Injection protection (usar Eloquent)
- XSS prevention (usar {{ }} en Blade)
- Authorization con Policies/Gates
- Sanitización de datos de usuario
- Validar y sanitizar archivos subidos
- HTTPS en producción

## Testing (opcional pero recomendado)
- Feature Tests para endpoints
- Unit Tests para Services
- Factories para datos de prueba

## Base de Datos
- Migrations para cualquier cambio de schema
- Seeders para datos iniciales/testing
- Usar Foreign Keys cuando sea apropiado
- Indexes en columnas de búsqueda frecuente
- Soft deletes cuando sea necesario mantener historial

## Consideraciones de Hosting
- Assets en `public/` por requisito del servidor
- Variables de entorno en `.env` (nunca committear)
- Logs configurados apropiadamente
- Cache configurado según ambiente
- Optimizaciones de Laravel (config:cache, route:cache, view:cache)

## Ejemplo de Controller Bien Estructurado
```php
<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUserRequest;
use App\Services\UserService;
use Illuminate\Http\Request;

class UserController extends Controller
{
    protected $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    /**
     * Muestra el listado de usuarios
     */
    public function index()
    {
        $users = $this->userService->getAllUsers();
        return view('users.index', compact('users'));
    }

    /**
     * Almacena un nuevo usuario
     */
    public function store(StoreUserRequest $request)
    {
        try {
            $user = $this->userService->createUser($request->validated());
            return redirect()->route('users.index')
                ->with('success', 'Usuario creado exitosamente');
        } catch (\Exception $e) {
            return back()->with('error', 'Error al crear usuario: ' . $e->getMessage());
        }
    }
}
```

## Notas Adicionales
- Siempre proporcionar código completo y funcional
- Incluir todos los imports/namespaces necesarios
- Considerar manejo de errores en cada método
- Pensar en escalabilidad desde el inicio
- Documentar decisiones arquitectónicas importantes
