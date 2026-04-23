{{--
|--------------------------------------------------------------------------
| Sección 5: Configuración Adicional
|--------------------------------------------------------------------------
--}}
<div class="cp-section" id="section-extra">

    <div class="cp-section-header">
        <div class="cp-section-icon">
            <i data-lucide="settings"></i>
        </div>
        <h2 class="cp-section-title">Configuración Adicional</h2>
    </div>
    <p class="cp-section-desc">Ajustes opcionales para afinar el comportamiento de la IA.</p>

    {{-- Instrucciones especiales --}}
    <div class="cp-form-group">
        <div class="cp-label-row">
            <label for="special_instructions">Instrucciones Especiales para la IA <span
                    class="hint">(opcional)</span></label>
            <span class="help-tooltip">
                <span class="help-tooltip-icon">?</span>
                <span class="help-tooltip-bubble">
                    <strong>¿Para qué sirve?</strong>
                    Reglas adicionales en lenguaje natural para comportamientos muy específicos del caso.
                    <div class="example">📝 "Si el médico niega la baja, el paciente se indigna y amenaza con irse"
                    </div>
                    <div class="example">📝 "Hacia el final de la consulta, pregunta si tendrá que quedarse ingresado"
                    </div>
                </span>
            </span>
        </div>
        <textarea id="special_instructions" name="special_instructions"
            placeholder="Ej: Si el estudiante no pregunta por antecedentes familiares en los primeros 5 intercambios, el paciente menciona de pasada que su padre murió joven...">{{ old('special_instructions') }}</textarea>
    </div>

    {{-- Frases de límite --}}
    <div class="cp-form-group">
        <div class="cp-label-row">
            <label>Frases cuando no sabe algo <span class="required">*</span></label>
            <span class="help-tooltip">
                <span class="help-tooltip-icon">?</span>
                <span class="help-tooltip-bubble">
                    <strong>¿Para qué sirve?</strong>
                    Las frases que dirá el paciente cuando le preguntan por datos que no tiene y no puede improvisar. Hacen el personaje más
                    natural.
                    <div class="example">📝 "No lo sé, eso lo tenéis vosotros en el ordenador"</div>
                    <div class="example">📝 "Ni idea, nadie me ha dicho eso"</div>
                    <div class="example">📝 "No me acuerdo de esos números"</div>
                </span>
            </span>
        </div>
        <div class="cp-dynamic-list" id="frasesLimiteContainer">
            @foreach(old('frases_limite', []) as $i => $frase)
                <div class="cp-dynamic-item">
                    <div class="cp-dynamic-item-fields" style="grid-template-columns: 1fr;">
                        <div class="cp-dynamic-item-field">
                            <label>Frase</label>
                            <input type="text" name="frases_limite[{{ $i }}]" value="{{ $frase }}"
                                placeholder="Ej: No tengo ni la más menor idea de eso">
                        </div>
                    </div>
                    <button type="button" class="cp-btn-remove" onclick="removeItem(this)">
                        <i data-lucide="x"></i>
                    </button>
                </div>
            @endforeach
        </div>
        <button type="button" class="cp-btn-add" onclick="addFraseLimite()">
            <i data-lucide="plus"></i>
            Añadir Frase
        </button>
    </div>

    {{-- Ejemplo de coherencia --}}
    <div class="cp-form-group">
        <div class="cp-label-row">
            <label>Ejemplo de Coherencia <span class="required">*</span></label>
            <span class="help-tooltip">
                <span class="help-tooltip-icon">?</span>
                <span class="help-tooltip-bubble">
                    <strong>¿Para qué sirve?</strong>
                    Un ejemplo concreto que muestre la diferencia entre una respuesta coherente e incoherente del paciente a una misma pregunta. Para un correcto funcionamiento hay que poner una pregunta que se espere que pregunte el doctor y enseñar a la IA como debe responder a eso.
                    <div class="example">📝 Pregunta: "¿Qué ha desayunado hoy el niño?"</div>
                    <div class="example">✅ Coherente: "Apenas ha querido un poco de leche, está sin ganas."</div>
                    <div class="example">❌ Incoherente: "Se ha comido un tazón de cereales con fruta."</div>
                </span>
            </span>
        </div>
        <div class="cp-coherence-block">
            <div class="cp-form-group">
                <div class="cp-label-row">
                    <label for="ejemplo_coherencia_pregunta">Pregunta de ejemplo</label>
                </div>
                <input type="text" id="ejemplo_coherencia_pregunta" name="ejemplo_coherencia[pregunta]"
                    value="{{ old('ejemplo_coherencia.pregunta') }}"
                    placeholder="Ej: ¿Ha podido dormir bien esta noche?">
            </div>
            <div class="cp-coherence-row">
                <div class="cp-form-group">
                    <div class="cp-label-row">
                        <label for="ejemplo_coherencia_coherente" class="coherence-ok">✅ Respuesta coherente</label>
                    </div>
                    <textarea id="ejemplo_coherencia_coherente" name="ejemplo_coherencia[coherente]"
                        placeholder="Ej: Que va, doctor, ha estado toda la noche inquieto con la fiebre.">{{ old('ejemplo_coherencia.coherente') }}</textarea>
                </div>
                <div class="cp-form-group">
                    <div class="cp-label-row">
                        <label for="ejemplo_coherencia_incoherente" class="coherence-bad">❌ Respuesta
                            incoherente</label>
                    </div>
                    <textarea id="ejemplo_coherencia_incoherente" name="ejemplo_coherencia[incoherente]"
                        placeholder="Ej: Sí, ha dormido perfectamente toda la noche de un tirón.">{{ old('ejemplo_coherencia.incoherente') }}</textarea>
                </div>
            </div>
        </div>
    </div>

</div>