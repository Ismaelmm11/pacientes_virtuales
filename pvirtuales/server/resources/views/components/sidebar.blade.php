{{--
|--------------------------------------------------------------------------
| Componente: Sidebar de Navegación
|--------------------------------------------------------------------------
|
| Sidebar fijo a la izquierda con navegación adaptada al rol del usuario.
|
| IMPORTANTE: Cada enlace está protegido con @if(Route::has(...))
| para que no explote cuando la ruta aún no está definida
| (mientras se construye la app por fases).
|
| ROLES:
| Admin (Role::ADMIN_ID = 3) → Gestión global del sistema
| Profesor (Role::TEACHER_ID = 2) → Gestión de sus asignaturas y pacientes
| Alumno (Role::STUDENT_ID = 1) → Navegación por asignaturas y simulaciones
|
--}}

<aside class="sidebar" id="sidebar">

    {{-- ===================== LOGO ===================== --}}
    <div class="sidebar-header">
        <div class="sidebar-logo">
            <div class="sidebar-logo-icon">
                <i data-lucide="activity"></i>
            </div>
            <span class="sidebar-logo-text">Pacientes<span> Virtuales</span></span>
        </div>
    </div>

    {{-- ===================== USUARIO LOGUEADO ===================== --}}
    <div class="sidebar-user">
        <div class="sidebar-avatar">
            {{ strtoupper(substr(auth()->user()->first_name, 0, 1)) }}{{ strtoupper(substr(auth()->user()->last_name, 0, 1)) }}
        </div>
        <div class="sidebar-user-info">
            <div class="sidebar-user-name">{{ auth()->user()->full_name }}</div>
            <div class="sidebar-user-role">
                @if(auth()->user()->isAdmin())
                    Administrador
                @elseif(auth()->user()->isTeacher())
                    Profesor
                @else
                    Alumno
                @endif
            </div>
        </div>
    </div>

    {{-- ===================== NAVEGACIÓN ===================== --}}
    <nav class="sidebar-nav">

        {{-- -----------------------------------------------
        ADMIN
        ----------------------------------------------- --}}
        @if(auth()->user()->isAdmin())

            <div class="sidebar-nav-group">
                <div class="sidebar-nav-group-title">General</div>

                @if(Route::has('admin.dashboard'))
                    <a href="{{ route('admin.dashboard') }}"
                        class="sidebar-nav-item {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                        <i data-lucide="layout-dashboard"></i>
                        Dashboard
                    </a>
                @endif

                @if(Route::has('admin.analytics'))
                    <a href="{{ route('admin.analytics') }}"
                        class="sidebar-nav-item {{ request()->routeIs('admin.analytics') ? 'active' : '' }}">
                        <i data-lucide="bar-chart-3"></i>
                        Analíticas
                    </a>
                @endif
            </div>

            <div class="sidebar-nav-group">
                <div class="sidebar-nav-group-title">Plataforma</div>

                @if(Route::has('admin.users.index'))
                    <a href="{{ route('admin.users.index') }}"
                        class="sidebar-nav-item {{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
                        <i data-lucide="users"></i>
                        Usuarios
                    </a>
                @endif

                @if(Route::has('admin.subjects.index'))
                    <a href="{{ route('admin.subjects.index') }}"
                        class="sidebar-nav-item {{ request()->routeIs('admin.subjects.*') ? 'active' : '' }}">
                        <i data-lucide="book-open"></i>
                        Asignaturas
                    </a>
                @endif

                @if(Route::has('admin.patients.index'))
                    <a href="{{ route('admin.patients.index') }}"
                        class="sidebar-nav-item {{ request()->routeIs('admin.patients.*') ? 'active' : '' }}">
                        <i data-lucide="user-round"></i>
                        Pacientes
                    </a>
                @endif
            </div>

            <div class="sidebar-nav-group">
                <div class="sidebar-nav-group-title">Sistema</div>

                @if(Route::has('admin.ai-config'))
                    <a href="{{ route('admin.ai-config') }}"
                        class="sidebar-nav-item {{ request()->routeIs('admin.ai-config') ? 'active' : '' }}">
                        <i data-lucide="cpu"></i>
                        Config IA
                    </a>
                @endif

                @if(Route::has('admin.ai-judge'))
                    <a href="{{ route('admin.ai-judge') }}"
                        class="sidebar-nav-item {{ request()->routeIs('admin.ai-judge') ? 'active' : '' }}">
                        <i data-lucide="scale"></i>
                        Juez IA
                    </a>
                @endif
            </div>


            {{-- -----------------------------------------------
            PROFESOR
            ----------------------------------------------- --}}
        @elseif(auth()->user()->isTeacher())

            <div class="sidebar-nav-group">
                <div class="sidebar-nav-group-title">General</div>

                @if(Route::has('teacher.dashboard'))
                    <a href="{{ route('teacher.dashboard') }}"
                        class="sidebar-nav-item {{ request()->routeIs('teacher.dashboard') ? 'active' : '' }}">
                        <i data-lucide="layout-dashboard"></i>
                        Dashboard
                    </a>
                @endif
            </div>

            <div class="sidebar-nav-group">
                <div class="sidebar-nav-group-title">Mis Asignaturas</div>

                @if(Route::has('teacher.subjects.index'))
                    <a href="{{ route('teacher.subjects.index') }}"
                        class="sidebar-nav-item {{ request()->routeIs('teacher.subjects.*') ? 'active' : '' }}">
                        <i data-lucide="book-open"></i>
                        Asignaturas
                    </a>
                @endif

                @if(Route::has('teacher.patients.index'))
                    <a href="{{ route('teacher.patients.index') }}"
                        class="sidebar-nav-item {{ request()->routeIs('teacher.patients.*') ? 'active' : '' }}">
                        <i data-lucide="user-round"></i>
                        Pacientes
                    </a>
                @endif
            </div>

            <div class="sidebar-nav-group">
                <div class="sidebar-nav-group-title">Seguimiento</div>

                @if(Route::has('teacher.consultations.index'))
                    <a href="{{ route('teacher.consultations.index') }}"
                        class="sidebar-nav-item {{ request()->routeIs('teacher.consultations.*') ? 'active' : '' }}">
                        <i data-lucide="message-square"></i>
                        Consultas
                    </a>
                @endif

                @if(Route::has('teacher.results.index'))
                    <a href="{{ route('teacher.results.index') }}"
                        class="sidebar-nav-item {{ request()->routeIs('teacher.results.*') ? 'active' : '' }}">
                        <i data-lucide="clipboard-check"></i>
                        Resultados Tests
                    </a>
                @endif
            </div>

            {{-- -----------------------------------------------
            ALUMNO
            ----------------------------------------------- --}}
        @else

            <div class="sidebar-nav-group">
                <div class="sidebar-nav-group-title">General</div>

                @if(Route::has('student.dashboard'))
                    <a href="{{ route('student.dashboard') }}"
                        class="sidebar-nav-item {{ request()->routeIs('student.dashboard') ? 'active' : '' }}">
                        <i data-lucide="layout-dashboard"></i>
                        Dashboard
                    </a>
                @endif
            </div>

            <div class="sidebar-nav-group">
                <div class="sidebar-nav-group-title">Mis Asignaturas</div>

                @if(Route::has('student.subjects.index'))
                    <a href="{{ route('student.subjects.index') }}"
                        class="sidebar-nav-item {{ request()->routeIs('student.subjects.*') ? 'active' : '' }}">
                        <i data-lucide="book-open"></i>
                        Asignaturas
                    </a>
                @endif

                @if(Route::has('student.patients.index'))
                    <a href="{{ route('student.patients.index') }}"
                        class="sidebar-nav-item {{ request()->routeIs('student.patients.*') ? 'active' : '' }}">
                        <i data-lucide="stethoscope"></i>
                        Practicar
                    </a>
                @endif
            </div>

            <div class="sidebar-nav-group">
                <div class="sidebar-nav-group-title">Mi Historial</div>

                @if(Route::has('student.consultations.index'))
                    <a href="{{ route('student.consultations.index') }}"
                        class="sidebar-nav-item {{ request()->routeIs('student.consultations.*') ? 'active' : '' }}">
                        <i data-lucide="message-square"></i>
                        Mis Consultas
                    </a>
                @endif

                @if(Route::has('student.results.index'))
                    <a href="{{ route('student.results.index') }}"
                        class="sidebar-nav-item {{ request()->routeIs('student.results.*') ? 'active' : '' }}">
                        <i data-lucide="clipboard-check"></i>
                        Mis Resultados
                    </a>
                @endif
            </div>

        @endif

    </nav>

    {{-- ===================== LOGOUT ===================== --}}
    <div class="sidebar-footer">
        <form action="{{ route('logout') }}" method="POST">
            @csrf
            <button type="submit" class="sidebar-logout">
                <i data-lucide="log-out"></i>
                Cerrar Sesión
            </button>
        </form>
    </div>

</aside>