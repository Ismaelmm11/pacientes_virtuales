{{-- 
|--------------------------------------------------------------------------
| Componente: Navegación del Wizard
|--------------------------------------------------------------------------
|
| Este partial gestiona el flujo entre paneles. Nota cómo los botones 
| no son de tipo 'submit' (excepto el final) para evitar que el 
| formulario se envíe antes de tiempo.
|
--}}

<div class="wizard-nav">
    {{-- 
        Indicador de progreso textual: 
        Se actualiza dinámicamente mediante el JS (updateStepInfo) 
        para reflejar el índice real. 
    --}}
    <span class="wizard-nav-info">Paso 1 de 5</span>

    <div class="wizard-nav-buttons">
        {{-- 
            BOTÓN ANTERIOR:
            Usa onclick="prevStep()" para disparar la transición hacia atrás.
            El CSS lo ocultará automáticamente en el Paso 1.
        --}}
        <button type="button" class="btn-wizard-prev" onclick="prevStep()">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <polyline points="15 18 9 12 15 6"/>
            </svg>
            Anterior
        </button>

        {{-- 
            BOTÓN SIGUIENTE:
            El motor principal de avance. En el Paso 5, el JS ocultará este botón
            y mostrará el botón de 'Crear Paciente'.
        --}}
        <button type="button" class="btn-wizard-next" onclick="nextStep()">
            Siguiente
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <polyline points="9 18 15 12 9 6"/>
            </svg>
        </button>

        {{-- 
            BOTÓN DE ENVÍO FINAL:
            Este es el único botón de tipo 'submit'. Solo aparece en el último paso.
            Al pulsarlo, Laravel recibe todos los datos de los 5 pasos a la vez.
        --}}
        <button type="submit" class="btn-submit" style="display: none;">
            <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/>
                <polyline points="17 21 17 13 7 13 7 21"/>
                <polyline points="7 3 7 8 15 8"/>
            </svg>
            Crear Paciente y Generar Prompt
        </button>
    </div>
</div>