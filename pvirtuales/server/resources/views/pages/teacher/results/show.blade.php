<x-layouts.app>

    <x-slot name="title">Resultado — {{ $attempt->patient?->case_title }}</x-slot>

    <x-slot name="styles">
        <link href="{{ asset('css/dashboard.css') }}" rel="stylesheet">
        <link href="{{ asset('css/modal.css') }}" rel="stylesheet">
    </x-slot>

    <x-slot name="topbar">
        <div class="topbar">
            <div class="topbar-left">
                <div class="topbar-title">Resultado del Test</div>
                <div class="topbar-subtitle">
                    {{ $attempt->user?->full_name }} — {{ $attempt->patient?->case_title }}
                </div>
            </div>
            <div class="topbar-right">
                <a href="{{ route('teacher.results.index') }}" class="btn btn-ghost btn-sm">
                    <i data-lucide="arrow-left"></i>
                    Volver
                </a>
            </div>
        </div>
    </x-slot>

    @if(session('success'))
        <div class="alert alert-success mb-md">
            <i data-lucide="check-circle"></i>
            {{ session('success') }}
        </div>
    @endif

    {{-- Info general --}}
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-card-icon primary"><i data-lucide="user"></i></div>
            <div class="stat-card-info">
                <div class="stat-card-value">{{ $attempt->user?->full_name ?? '—' }}</div>
                <div class="stat-card-label">Alumno</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-card-icon secondary"><i data-lucide="user-round"></i></div>
            <div class="stat-card-info">
                <div class="stat-card-value">{{ $attempt->patient?->case_title ?? '—' }}</div>
                <div class="stat-card-label">Paciente</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-card-icon warning"><i data-lucide="calendar"></i></div>
            <div class="stat-card-info">
                <div class="stat-card-value">{{ $attempt->submitted_at->format('d/m/Y H:i') }}</div>
                <div class="stat-card-label">Enviado el</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-card-icon {{ $attempt->final_score !== null ? ($attempt->final_score >= 5 ? 'success' : 'danger') : 'secondary' }}">
                <i data-lucide="bar-chart-2"></i>
            </div>
            <div class="stat-card-info">
                <div class="stat-card-value">
                    {{ $attempt->final_score !== null ? number_format($attempt->final_score, 2) . ' / 10' : 'Pendiente' }}
                </div>
                <div class="stat-card-label">Nota final</div>
            </div>
        </div>
    </div>

    {{-- Respuestas --}}
    @php
        $hasOpenPending = $attempt->answers->contains(fn($a) => $a->question?->question_type === 'open_ended' && $a->score === null);
    @endphp

    <form action="{{ route('teacher.results.grade', $attempt) }}" method="POST">
        @csrf

        @foreach($attempt->answers as $index => $answer)
            @php $question = $answer->question; @endphp
            <div class="card mt-lg">
                <div class="card-header">
                    <div class="card-header-title">
                        <span class="badge badge-secondary">{{ $index + 1 }}</span>
                        {{ $question?->question_text ?? 'Pregunta eliminada' }}
                    </div>
                    <div>
                        @if($question)
                            <span class="badge badge-secondary">{{ $question->type_label }}</span>
                        @endif
                        @if($answer->score !== null)
                            <span class="badge {{ $answer->score > 0 ? 'badge-success' : 'badge-danger' }}">
                                {{ number_format($answer->score, 2) }} pts
                            </span>
                        @else
                            <span class="badge badge-warning">Sin corregir</span>
                        @endif
                    </div>
                </div>

                <div style="padding: var(--space-md) var(--space-lg);">

                    <div class="mb-md">
                        <div class="text-muted text-sm mb-xs">Respuesta del alumno:</div>
                        <div style="background: var(--color-bg-secondary); border-radius: var(--radius-md); padding: var(--space-md);">
                            {{ $answer->given_answer ?? '(sin respuesta)' }}
                        </div>
                    </div>

                    @if($question && $question->question_type !== 'open_ended')
                        <div class="mb-md">
                            <div class="text-muted text-sm mb-xs">Respuesta correcta:</div>
                            <div style="background: var(--color-bg-secondary); border-radius: var(--radius-md); padding: var(--space-md);">
                                {{ $question->correct_answer }}
                            </div>
                        </div>

                        @if($answer->feedback)
                            <div class="text-muted text-sm">
                                <strong>Feedback:</strong> {{ $answer->feedback }}
                            </div>
                        @endif
                    @endif

                    @if($question && $question->question_type === 'open_ended')
                        <div>
                            <label class="text-sm text-muted mb-xs" style="display: block;">
                                Puntuación (máx. {{ $question->points ?? 10 }} pts):
                            </label>
                            <input type="number"
                                   name="scores[{{ $answer->id }}]"
                                   min="0"
                                   max="{{ $question->points ?? 10 }}"
                                   step="0.01"
                                   value="{{ $answer->score ?? '' }}"
                                   style="width: 120px; padding: var(--space-sm); border: 1px solid var(--color-border); border-radius: var(--radius-md); background: var(--color-bg-secondary); color: var(--color-text);">
                        </div>
                    @endif

                </div>
            </div>
        @endforeach

        @if($hasOpenPending)
            <div class="mt-lg" style="display: flex; justify-content: flex-end;">
                <button type="submit" class="btn btn-primary">
                    <i data-lucide="save"></i>
                    Guardar corrección
                </button>
            </div>
        @endif

    </form>

    {{-- Transcripción --}}
    @if($attempt->interview_transcript)
        <div class="card mt-lg">
            <div class="card-header">
                <div class="card-header-title">
                    <i data-lucide="message-square"></i>
                    Transcripción de la consulta
                </div>
            </div>
            <div style="padding: var(--space-md) var(--space-lg); max-height: 400px; overflow-y: auto;">
                @foreach($attempt->interview_transcript as $msg)
                    @if($msg['role'] === 'system') @continue @endif
                    <div style="margin-bottom: var(--space-md);">
                        <div class="text-sm text-muted mb-xs">
                            {{ $msg['role'] === 'user' ? '👨‍⚕️ Alumno' : '🧑‍⚕️ Paciente' }}
                        </div>
                        <div style="background: var(--color-bg-secondary); border-radius: var(--radius-md); padding: var(--space-sm) var(--space-md);">
                            {{ $msg['content'] }}
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

</x-layouts.app>
