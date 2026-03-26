{{-- resources/views/emails/student-invited.blade.php --}}
<x-emails.layouts.base>

    {{-- Saludo --}}
    <div class="email-greeting">
        ¡Hola! 👋
    </div>

    <p class="email-intro">
        El profesor <strong>{{ $teacher->full_name }}</strong> te ha inscrito en una asignatura 
        en <strong>Pacientes Virtuales</strong>, una plataforma de simulación clínica. 
        Para acceder necesitas crear tu cuenta — solo te llevará un momento.
    </p>

    {{-- Tarjeta con info de la asignatura --}}
    <div class="email-info-card">
        <div class="email-info-row">
            <span class="email-info-label">📚 Asignatura</span>
            <span class="email-info-value">{{ $asignatura->name }}</span>
        </div>
        <div class="email-info-row">
            <span class="email-info-label">🏫 Institución</span>
            <span class="email-info-value">{{ $asignatura->institution }}</span>
        </div>
        <div class="email-info-row">
            <span class="email-info-label">🔖 Código</span>
            <span class="email-info-value">{{ $asignatura->code }}</span>
        </div>
        <div class="email-info-row">
            <span class="email-info-label">👨‍🏫 Profesor</span>
            <span class="email-info-value">{{ $teacher->full_name }}</span>
        </div>
    </div>

    {{-- Botón de registro --}}
    <div class="email-btn-wrapper">
        <a href="{{ route('register.form', $token) }}" class="email-btn">
            Crear mi cuenta
        </a>
    </div>

    <div class="email-divider"></div>

    {{-- Nota --}}
    <div class="email-note">
        ⚠️ Este enlace de registro es personal e intransferible. Si crees que has 
        recibido este email por error, puedes ignorarlo sin ningún problema.
    </div>

</x-emails.layouts.base>