{{-- resources/views/emails/student-enrolled.blade.php --}}
<x-emails.layouts.base>

    {{-- Saludo --}}
    <div class="email-greeting">
        ¡Hola, {{ $student->first_name }}! 👋
    </div>

    <p class="email-intro">
        El profesor <strong>{{ $teacher->full_name }}</strong> te ha inscrito en una nueva asignatura 
        en la plataforma <strong>Pacientes Virtuales</strong>. Ya puedes acceder y comenzar a practicar 
        tus habilidades clínicas con pacientes virtuales.
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

    {{-- Botón de acceso --}}
    <div class="email-btn-wrapper">
        <a href="{{ route('login') }}" class="email-btn">
            Acceder a la plataforma
        </a>
    </div>

    <div class="email-divider"></div>

    {{-- Nota de error --}}
    <div class="email-note">
        ⚠️ Si crees que has sido inscrito por error o no reconoces a este profesor, 
        responde a este email y lo resolveremos a la mayor brevedad posible.
    </div>

</x-emails.layouts.base>