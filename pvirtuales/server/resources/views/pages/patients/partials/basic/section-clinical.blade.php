{{--
|--------------------------------------------------------------------------
| Sección 3: La Consulta
|--------------------------------------------------------------------------
--}}
<div class="cp-section" id="section-clinical">

    <div class="cp-section-header">
        <div class="cp-section-icon">
            <i data-lucide="activity"></i>
        </div>
        <h2 class="cp-section-title">La Consulta</h2>
    </div>
    <p class="cp-section-desc">Define qué dice el paciente (o el acompañante), qué le pasa y qué debe descubrir el
        estudiante.</p>

    {{-- Frase inicial --}}
    <div class="cp-form-group">
        <div class="cp-label-row">
            <label for="frase_inicial">Frase Inicial <span class="required">*</span></label>
            <span class="help-tooltip">
                <span class="help-tooltip-icon">?</span>
                <span class="help-tooltip-bubble">
                    <strong>¿Para qué sirve?</strong>
                    Es lo primero que dirá el paciente al iniciar la consulta. La IA la reproduce textualmente.
                    <div class="example">📝 "Ay, doctor, vengo fatal. Tengo la espalda destrozada."</div>
                    <div class="example">📝 "A ver, doctor, rapidito. Me ha obligado mi mujer a venir, pero yo estoy
                        perfectamente."</div>
                </span>
            </span>
        </div>
        <textarea id="frase_inicial" name="frase_inicial" required
            placeholder="Ej: Buenos días, doctor. Vengo porque llevo unos días con un dolor en el pecho que me tiene preocupado.">{{ old('frase_inicial') }}</textarea>
    </div>

    {{-- Motivo de consulta --}}
    <div class="cp-form-group">
        <div class="cp-label-row">
            <label for="motivo_consulta">Motivo Principal de Consulta <span class="required">*</span></label>
            <span class="help-tooltip">
                <span class="help-tooltip-icon">?</span>
                <span class="help-tooltip-bubble">
                    <strong>¿Para qué sirve?</strong>
                    La respuesta a la pregunta "¿qué le trae por aquí?". Escríbelo como lo diría el paciente.
                    <div class="example">📝 "¡Que no me pasa nada! Son tonterías de mi mujer."</div>
                    <div class="example">📝 "Me duele mucho la rodilla derecha desde que me caí el martes."</div>
                </span>
            </span>
        </div>
        <textarea id="motivo_consulta" name="motivo_consulta" required
            placeholder="Ej: Me duele el pecho desde hace tres días, sobre todo cuando subo escaleras. Se me va al brazo izquierdo.">{{ old('motivo_consulta') }}</textarea>
    </div>

    {{-- Síntomas con revelación --}}
    <div class="cp-form-group">
        <div class="cp-label-row">
            <label>Síntomas</label>
            <span class="help-tooltip">
                <span class="help-tooltip-icon">?</span>
                <span class="help-tooltip-bubble">
                    <strong>¿Para qué sirve?</strong>
                    Los síntomas del paciente. Para cada uno defines cuándo lo revela: esto convierte la simulación en
                    un ejercicio educativo real.
                    <div class="example">📝 "Dolor en el pecho" → Espontáneamente</div>
                    <div class="example">📝 "Dificultad para respirar" → Si le preguntan</div>
                    <div class="example">📝 "Bebe 3-4 carajillos" → Miente → "Lo normal, lo que bebe cualquier español"
                    </div>
                </span>
            </span>
        </div>

        <div class="cp-dynamic-list" id="symptomsContainer">
            @php $existingSymptoms = old('symptoms', [[]]); @endphp
            @foreach($existingSymptoms as $i => $s)
                <div class="cp-dynamic-item">
                    <div class="cp-dynamic-item-fields">
                        <div class="cp-dynamic-item-field">
                            <label>Síntoma</label>
                            <input type="text" name="symptoms[{{ $i }}][name]" value="{{ $s['name'] ?? '' }}"
                                placeholder="Ej: Dolor en el pecho al hacer esfuerzo">
                        </div>
                        <div class="cp-dynamic-item-field">
                            <label>Cuándo lo revela</label>
                            <select name="symptoms[{{ $i }}][reveal]" class="reveal-select"
                                onchange="handleRevealChange(this)">
                                <option value="espontaneo" {{ ($s['reveal'] ?? 'espontaneo') == 'espontaneo' ? 'selected' : '' }}>Espontáneamente</option>
                                <option value="pregunta" {{ ($s['reveal'] ?? '') == 'pregunta' ? 'selected' : '' }}>Si le
                                    preguntan</option>
                                <option value="oculta" {{ ($s['reveal'] ?? '') == 'oculta' ? 'selected' : '' }}>Lo oculta
                                </option>
                                <option value="miente" {{ ($s['reveal'] ?? '') == 'miente' ? 'selected' : '' }}>Miente
                                </option>
                                <option value="exagera" {{ ($s['reveal'] ?? '') == 'exagera' ? 'selected' : '' }}>Exagera
                                </option>
                            </select>
                        </div>
                        <div class="cp-lie-field {{ ($s['reveal'] ?? '') == 'miente' ? 'visible' : '' }}"
                            id="lie_symptoms_{{ $i }}">
                            <label>¿Qué dice en su lugar?</label>
                            <input type="text" name="symptoms[{{ $i }}][lie_text]" value="{{ $s['lie_text'] ?? '' }}"
                                placeholder="Vacío = la IA improvisa la mentira">
                            <p class="cp-lie-hint">Si se deja vacío, la IA inventará la mentira de forma coherente.</p>
                        </div>
                    </div>
                    <button type="button" class="cp-btn-remove" onclick="removeItem(this)"
                        style="{{ count($existingSymptoms) <= 1 ? 'visibility: hidden;' : '' }}">
                        <i data-lucide="x"></i>
                    </button>
                </div>
            @endforeach
        </div>

        <button type="button" class="cp-btn-add" onclick="addSymptom()">
            <i data-lucide="plus"></i>
            Añadir Síntoma
        </button>
    </div>

    <hr class="cp-section-divider">

    {{-- Diagnóstico real --}}
    <div class="cp-form-group">
        <div class="cp-label-row">
            <label for="real_diagnosis">Diagnóstico Real <span class="required">*</span></label>
            <span class="help-tooltip">
                <span class="help-tooltip-icon">?</span>
                <span class="help-tooltip-bubble">
                    <strong>¿Para qué sirve?</strong>
                    El diagnóstico que el paciente NO sabe. Se lo damos a la IA para mantener coherencia interna, pero
                    <strong>nunca lo verbalizará</strong> al estudiante.
                    <div class="example">📝 "Síndrome coronario agudo — Angina inestable"</div>
                    <div class="example">📝 "Simulación. No tiene patología real. Quiere la baja."</div>
                </span>
            </span>
        </div>
        <input type="text" id="real_diagnosis" name="real_diagnosis" value="{{ old('real_diagnosis') }}"
            placeholder="Ej: Síndrome coronario agudo — Angina inestable" required>
    </div>

    {{-- Antecedentes médicos --}}
    <div class="cp-form-group">
        <div class="cp-label-row">
            <label for="medical_history">Antecedentes Médicos <span class="hint">(opcional)</span></label>
            <span class="help-tooltip">
                <span class="help-tooltip-icon">?</span>
                <span class="help-tooltip-bubble">
                    <strong>¿Para qué sirve?</strong>
                    Enfermedades previas, cirugías, alergias. Solo los mencionará si se lo preguntan.
                    <div class="example">📝 "Hipertensión arterial diagnosticada hace 5 años."</div>
                    <div class="example">📝 "Ingreso por neumonía hace un año. Alérgico a la penicilina."</div>
                </span>
            </span>
        </div>
        <textarea id="medical_history" name="medical_history"
            placeholder="Ej: Hipertensión arterial hace 10 años, diabetes tipo 2. Alérgico a la penicilina.">{{ old('medical_history') }}</textarea>
    </div>

    {{-- Medicación actual --}}
    <div class="cp-form-group">
        <div class="cp-label-row">
            <label>Medicación Actual <span class="hint">(opcional)</span></label>
            <span class="help-tooltip">
                <span class="help-tooltip-icon">?</span>
                <span class="help-tooltip-bubble">
                    <strong>¿Para qué sirve?</strong>
                    Fármacos que toma el paciente y su adherencia.
                    <div class="example">📝 Enalapril 10mg → "Cuando me duele la cabeza"</div>
                    <div class="example">📝 Metformina 850mg → "Una vez al día"</div>
                </span>
            </span>
        </div>
        <div class="cp-dynamic-list" id="medicationsContainer">
            @foreach(old('medications', []) as $i => $med)
                <div class="cp-dynamic-item">
                    <div class="cp-dynamic-item-fields">
                        <div class="cp-dynamic-item-field">
                            <label>Medicamento</label>
                            <input type="text" name="medications[{{ $i }}][name]" value="{{ $med['name'] ?? '' }}"
                                placeholder="Ej: Enalapril 10mg">
                        </div>
                        <div class="cp-dynamic-item-field">
                            <label>Cuándo lo toma</label>
                            <input type="text" name="medications[{{ $i }}][frequency]" value="{{ $med['frequency'] ?? '' }}"
                                placeholder="Ej: Una vez al día / Cuando me duele la cabeza">
                        </div>
                    </div>
                    <button type="button" class="cp-btn-remove" onclick="removeItem(this)">
                        <i data-lucide="x"></i>
                    </button>
                </div>
            @endforeach
        </div>

        <button type="button" class="cp-btn-add" onclick="addMedication()">
            <i data-lucide="plus"></i>
            Añadir Medicamento
        </button>
    </div>

    {{-- Vicios / Hábitos tóxicos --}}
    <div class="cp-form-group">
        <div class="cp-label-row">
            <label>Vicios / Hábitos Tóxicos <span class="hint">(opcional)</span></label>
            <span class="help-tooltip">
                <span class="help-tooltip-icon">?</span>
                <span class="help-tooltip-bubble">
                    <strong>¿Para qué sirve?</strong>
                    Los pacientes suelen minimizar o mentir sobre esto. Gran valor pedagógico.
                    <div class="example">📝 Tabaco → "2 paquetes/día desde los 18" → Si le preguntan</div>
                    <div class="example">📝 Alcohol → "3-4 carajillos al día" → Miente → "Lo normal"</div>
                </span>
            </span>
        </div>
        <div class="cp-dynamic-list" id="vicesContainer">
            @foreach(old('vices', []) as $i => $vice)
                <div class="cp-dynamic-item">
                    <div class="cp-dynamic-item-fields">
                        <div class="cp-dynamic-item-field">
                            <label>Vicio</label>
                            <input type="text" name="vices[{{ $i }}][name]" value="{{ $vice['name'] ?? '' }}"
                                placeholder="Ej: Tabaco: 2 paquetes al día">
                        </div>
                        <div class="cp-dynamic-item-field">
                            <label>Cuándo lo revela</label>
                            <select name="vices[{{ $i }}][reveal]" class="reveal-select"
                                onchange="handleRevealChange(this)">
                                <option value="espontaneo" {{ ($vice['reveal'] ?? 'espontaneo') == 'espontaneo' ? 'selected' : '' }}>Espontáneamente</option>
                                <option value="pregunta" {{ ($vice['reveal'] ?? '') == 'pregunta' ? 'selected' : '' }}>Si le
                                    preguntan</option>
                                <option value="oculta" {{ ($vice['reveal'] ?? '') == 'oculta' ? 'selected' : '' }}>Lo oculta
                                </option>
                                <option value="miente" {{ ($vice['reveal'] ?? '') == 'miente' ? 'selected' : '' }}>Miente
                                </option>
                            </select>
                        </div>
                        <div class="cp-lie-field {{ ($vice['reveal'] ?? '') == 'miente' ? 'visible' : '' }}">
                            <label>¿Qué dice en su lugar?</label>
                            <input type="text" name="vices[{{ $i }}][lie_text]" value="{{ $vice['lie_text'] ?? '' }}"
                                placeholder="Vacío = la IA improvisa">
                            <p class="cp-lie-hint">Si se deja vacío, la IA inventará la mentira de forma coherente.</p>
                        </div>
                    </div>
                    <button type="button" class="cp-btn-remove" onclick="removeItem(this)">
                        <i data-lucide="x"></i>
                    </button>
                </div>
            @endforeach
        </div>

        <button type="button" class="cp-btn-add" onclick="addVice()">
            <i data-lucide="plus"></i>
            Añadir Vicio
        </button>
    </div>

    {{-- Antecedentes familiares --}}
    <div class="cp-form-group">
        <div class="cp-label-row">
            <label for="family_history">Antecedentes Familiares <span class="hint">(opcional)</span></label>
            <span class="help-tooltip">
                <span class="help-tooltip-icon">?</span>
                <span class="help-tooltip-bubble">
                    <strong>¿Para qué sirve?</strong>
                    Historial médico de familiares directos.
                    <div class="example">📝 "Padre murió del corazón a los 52. Madre con diabetes tipo 2."</div>
                </span>
            </span>
        </div>
        <textarea id="family_history" name="family_history"
            placeholder="Ej: Padre fallecido de infarto a los 52. Madre con diabetes tipo 2.">{{ old('family_history') }}</textarea>
    </div>

</div>