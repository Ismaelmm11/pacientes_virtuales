{{--
|--------------------------------------------------------------------------
| Editar Asignatura
|--------------------------------------------------------------------------
|
| DATOS RECIBIDOS DEL CONTROLADOR:
| $subject → Subject a editar
|
--}}


<x-layouts.app>

    <x-slot name="title">Editar {{ $subject->name }}</x-slot>

    <x-slot name="styles">
        <link href="{{ asset('css/create-patient.css') }}" rel="stylesheet">
    </x-slot>

    <x-slot name="topbar">
        <div class="topbar">
            <div class="topbar-left">
                <div class="topbar-title">Editar Asignatura</div>
                <div class="topbar-subtitle">{{ $subject->name }}</div>
            </div>
            <div class="topbar-right">
                <a href="{{ route('teacher.subjects.show', $subject) }}" class="btn btn-ghost btn-sm">
                    <i data-lucide="arrow-left"></i>
                    Volver
                </a>
            </div>
        </div>
    </x-slot>

    <form action="{{ route('teacher.subjects.update', $subject) }}" method="POST" class="form">
        @csrf
        @method('PUT')

        <div class="cp-section">

            <div class="cp-section-header">
                <div class="cp-section-icon">
                    <i data-lucide="book-open"></i>
                </div>
                <h2 class="cp-section-title">Datos de la Asignatura</h2>
            </div>
            <p class="cp-section-desc">Define la información básica de la asignatura.</p>

            {{-- Nombre --}}
            <div class="cp-form-group">
                <div class="cp-label-row">
                    <label for="name">Nombre <span class="required">*</span></label>
                </div>
                <input type="text" id="name" name="name" value="{{ old('name', $subject->name) }}" placeholder="Ej: Semiología Médica"
                    required>
                @error('name')
                    <div class="form-error">{{ $message }}</div>
                @enderror
            </div>

            {{-- Código --}}
            <div class="cp-form-group">
                <div class="cp-label-row">
                    <label for="code">Código <span class="required">*</span></label>
                </div>
                <input type="text" id="code" name="code" value="{{ old('code', $subject->code) }}" placeholder="Ej: MED-301" required>
                @error('code')
                    <div class="form-error">{{ $message }}</div>
                @enderror
            </div>

            {{-- Institución --}}
            <div class="cp-form-group">
                <div class="cp-label-row">
                    <label for="institution">Institución <span class="required">*</span></label>
                </div>
                <input type="text" id="institution" name="institution" value="{{ old('institution', $subject->institution) }}"
                    placeholder="Ej: Universidad de Valencia" required>
                @error('institution')
                    <div class="form-error">{{ $message }}</div>
                @enderror
            </div>

            {{-- Botones --}}
            <div class="form-actions">
                <a href="{{ route('teacher.subjects.index') }}" class="btn btn-ghost">
                    Cancelar
                </a>
                <button type="submit" class="btn btn-primary">
                    <i data-lucide="save"></i>
                    Guardar cambios
                </button>
            </div>

        </div>
    </form>

</x-layouts.app>