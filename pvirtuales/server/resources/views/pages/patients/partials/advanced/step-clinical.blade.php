{{-- Paso 3: Cuadro Clínico (Avanzado) --}}
<div class="section">
    <h2 class="section-title">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M22 12h-4l-3 9L9 3l-3 9H2"/>
        </svg>
        Cuadro Clínico
    </h2>
    <p class="section-description">Define toda la información médica del caso con máximo detalle.</p>

    {{-- Frase inicial --}}
    <div class="form-group">
        <label for="frase_inicial">
            Frase Inicial del Paciente <span class="required">*</span>
            <span class="help-tooltip">
                <span class="help-tooltip-icon">?</span>
                <span class="help-tooltip-bubble">
                    <strong>¿Para qué sirve?</strong>
                    Lo primero que dirá al iniciar la consulta. La IA lo dirá textualmente. Incluye el motivo de consulta con sus propias palabras.
                    <div class="example">📝 "A ver, doctor, rapidito que tengo el camión en doble fila. Me ha obligado mi mujer a venir, pero yo estoy perfectamente."</div>
                </span>
            </span>
        </label>
        <textarea id="frase_inicial" name="frase_inicial" required
                  placeholder="Ej: A ver, doctor, rapidito que tengo el camión en doble fila. Me ha obligado mi mujer a venir.">{{ old('frase_inicial') }}</textarea>
    </div>

    {{-- Historia narrativa --}}
    <div class="form-group">
        <label for="historia_narrativa">
            Historia Narrativa <span class="hint">(opcional)</span>
            <span class="help-tooltip">
                <span class="help-tooltip-icon">?</span>
                <span class="help-tooltip-bubble">
                    <strong>¿Para qué sirve?</strong>
                    El relato completo desde la perspectiva del paciente. No la verdad médica, sino lo que el paciente CREE que le pasa y cómo lo cuenta.
                    <div class="example">📝 "Empezó el lunes justo antes de ir a la oficina. Mi jefe me había gritado el viernes y creo que es la tensión acumulada. Me dio un crujido horrible al levantar una caja."</div>
                </span>
            </span>
        </label>
        <textarea id="historia_narrativa" name="historia_narrativa" class="textarea-large"
                  placeholder="Ej: Cuenta toda la historia desde la perspectiva del paciente, incluyendo fechas y lo que cree que le pasa.">{{ old('historia_narrativa') }}</textarea>
    </div>

    {{-- Síntomas detallados --}}
    <div class="form-group">
        <label>
            Síntomas
            <span class="help-tooltip">
                <span class="help-tooltip-icon">?</span>
                <span class="help-tooltip-bubble">
                    <strong>¿Para qué sirve?</strong>
                    Cada síntoma con intensidad, agravantes, atenuantes y regla de revelación.
                    <div class="example">📝 Disnea | Intensidad 6 | Agrava: esfuerzo | Mejora: sentado | Revela: solo si preguntan</div>
                </span>
            </span>
        </label>
        <div class="dynamic-list" id="symptomsContainer">
            <div class="dynamic-item">
                <div class="item-fields symptom-fields-advanced">
                    <div class="item-field">
                        <label>
                            Síntoma
                            <span class="help-tooltip">
                                <span class="help-tooltip-icon">?</span>
                                <span class="help-tooltip-bubble">
                                    <strong>¿Para qué sirve?</strong>
                                    Describe el síntoma tal como lo expresaría el paciente.
                                    <div class="example">📝 "Dolor torácico opresivo"</div>
                                    <div class="example">📝 "Se me duerme el brazo izquierdo"</div>
                                </span>
                            </span>
                        </label>
                        <input type="text" name="symptoms[0][name]" value="{{ old('symptoms.0.name') }}" placeholder="Ej: Dolor torácico">
                    </div>
                    <div class="item-field">
                        <label>
                            Intensidad (1-10)
                            <span class="help-tooltip">
                                <span class="help-tooltip-icon">?</span>
                                <span class="help-tooltip-bubble">
                                    <strong>¿Para qué sirve?</strong>
                                    Escala del dolor. La IA lo usará cuando el estudiante pregunte "del 1 al 10, ¿cuánto le duele?".
                                    <div class="example">📝 3 = molestia leve, 7 = dolor importante, 10 = insoportable</div>
                                </span>
                            </span>
                        </label>
                        <input type="number" name="symptoms[0][intensity]" value="{{ old('symptoms.0.intensity') }}" min="1" max="10" placeholder="Ej: 7">
                    </div>
                    <div class="item-field">
                        <label>
                            Agravantes
                            <span class="help-tooltip">
                                <span class="help-tooltip-icon">?</span>
                                <span class="help-tooltip-bubble">
                                    <strong>¿Para qué sirve?</strong>
                                    Qué empeora el síntoma. Clave para el diagnóstico diferencial.
                                    <div class="example">📝 "Esfuerzo físico, estrés, comidas copiosas"</div>
                                </span>
                            </span>
                        </label>
                        <input type="text" name="symptoms[0][aggravating]" value="{{ old('symptoms.0.aggravating') }}" placeholder="Ej: Esfuerzo físico, estrés">
                    </div>
                    <div class="item-field">
                        <label>
                            Atenuantes
                            <span class="help-tooltip">
                                <span class="help-tooltip-icon">?</span>
                                <span class="help-tooltip-bubble">
                                    <strong>¿Para qué sirve?</strong>
                                    Qué mejora el síntoma. Ayuda al estudiante a orientar el diagnóstico.
                                    <div class="example">📝 "Reposo, sentarse, nitroglicerina sublingual"</div>
                                </span>
                            </span>
                        </label>
                        <input type="text" name="symptoms[0][relieving]" value="{{ old('symptoms.0.relieving') }}" placeholder="Ej: Reposo, sentarse">
                    </div>
                    <div class="item-field">
                        <label>
                            Revelación
                            <span class="help-tooltip">
                                <span class="help-tooltip-icon">?</span>
                                <span class="help-tooltip-bubble">
                                    <strong>¿Para qué sirve?</strong>
                                    Controla CUÁNDO el paciente comparte este síntoma. Es la clave pedagógica.
                                    <div class="example">📝 "Espontáneamente" → Lo dice sin que le pregunten</div>
                                    <div class="example">📝 "Miente" → Dice otra cosa para ocultar la verdad</div>
                                </span>
                            </span>
                        </label>
                        <select name="symptoms[0][reveal]" class="reveal-select" onchange="handleRevealChange(this)">
                            <option value="espontaneo">Espontáneamente</option>
                            <option value="pregunta_directa">Solo si le preguntan directamente</option>
                            <option value="pregunta_relacionada">Si preguntan algo relacionado</option>
                            <option value="oculta">Lo oculta (no lo admitirá)</option>
                            <option value="miente">Miente sobre esto</option>
                        </select>
                    </div>
                    <div class="lie-field {{ old('symptoms.0.reveal') == 'miente' ? 'visible' : '' }}">
                        <label>
                            ¿Qué dice en su lugar?
                            <span class="help-tooltip">
                                <span class="help-tooltip-icon">?</span>
                                <span class="help-tooltip-bubble">
                                    <strong>¿Para qué sirve?</strong>
                                    La mentira concreta. Si lo dejas vacío, la IA inventará una coherente.
                                    <div class="example">📝 "Dice que no le duele nada"</div>
                                </span>
                            </span>
                        </label>
                        <input type="text" name="symptoms[0][lie_text]" value="{{ old('symptoms.0.lie_text') }}" placeholder="Vacío = la IA improvisa">
                        <p class="lie-hint">Si se deja vacío, la IA inventará la mentira de forma coherente.</p>
                    </div>
                </div>
                <button type="button" class="btn-remove-item" onclick="removeItem(this)" style="visibility: hidden;">✕</button>
            </div>
        </div>
        <button type="button" class="btn-add-item" onclick="addSymptom()">
            <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            Añadir Síntoma
        </button>
    </div>

    {{-- Antecedentes: Enfermedades --}}
    <div class="form-group">
        <label>
            Enfermedades Previas <span class="hint">(opcional)</span>
            <span class="help-tooltip">
                <span class="help-tooltip-icon">?</span>
                <span class="help-tooltip-bubble">
                    <strong>¿Para qué sirve?</strong>
                    Historial de enfermedades del paciente. El estudiante debe preguntar por esto.
                    <div class="example">📝 "Hipertensión arterial" | Hace 5 años | Revela: solo si preguntan</div>
                </span>
            </span>
        </label>
        <div class="dynamic-list" id="diseasesContainer"></div>
        <button type="button" class="btn-add-item" onclick="addDisease()">
            <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            Añadir Enfermedad
        </button>
    </div>

    {{-- Antecedentes: Cirugías --}}
    <div class="form-group">
        <label>
            Cirugías Previas <span class="hint">(opcional)</span>
            <span class="help-tooltip">
                <span class="help-tooltip-icon">?</span>
                <span class="help-tooltip-bubble">
                    <strong>¿Para qué sirve?</strong>
                    Historial quirúrgico del paciente. El estudiante debe preguntar por esto.
                    <div class="example">📝 "Apendicectomía" | Hace 10 años | Revela: espontáneamente</div>
                    <div class="example">📝 "Bypass gástrico" | Hace 3 años | Revela: solo si preguntan</div>
                </span>
            </span>
        </label>
        <div class="dynamic-list" id="surgeriesContainer"></div>
        <button type="button" class="btn-add-item" onclick="addSurgery()">
            <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            Añadir Cirugía
        </button>
    </div>

    {{-- Alergias --}}
    <div class="form-group">
        <label for="allergies">
            Alergias <span class="hint">(opcional)</span>
            <span class="help-tooltip">
                <span class="help-tooltip-icon">?</span>
                <span class="help-tooltip-bubble">
                    <strong>¿Para qué sirve?</strong>
                    Alergias conocidas del paciente (medicamentos, alimentos, etc.).
                    <div class="example">📝 "No tiene alergias conocidas" o "Alérgico a penicilina, le produjo un sarpullido hace 10 años"</div>
                </span>
            </span>
        </label>
        <textarea id="allergies" name="allergies" placeholder="Ej: No tiene alergias conocidas">{{ old('allergies') }}</textarea>
    </div>

    {{-- Medicación estructurada --}}
    <div class="form-group">
        <label>
            Medicación Actual <span class="hint">(opcional)</span>
            <span class="help-tooltip">
                <span class="help-tooltip-icon">?</span>
                <span class="help-tooltip-bubble">
                    <strong>¿Para qué sirve?</strong>
                    Lista de medicamentos con dosis, frecuencia y si se los toma bien. La adherencia es clave pedagógica.
                    <div class="example">📝 "Enalapril 10mg | 1/día | NO se lo toma bien: se lo salta si no le duele la cabeza | Revela: miente → 'Sí, me la tomo todos los días'"</div>
                </span>
            </span>
        </label>
        <div class="dynamic-list" id="medicationsContainer"></div>
        <button type="button" class="btn-add-item" onclick="addMedication()">
            <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            Añadir Medicamento
        </button>
    </div>

    {{-- Vicios --}}
    <div class="form-group">
        <label>
            Vicios / Hábitos Tóxicos <span class="hint">(opcional)</span>
            <span class="help-tooltip">
                <span class="help-tooltip-icon">?</span>
                <span class="help-tooltip-bubble">
                    <strong>¿Para qué sirve?</strong>
                    Tabaco, alcohol, otras sustancias. Los pacientes suelen minimizar o mentir sobre esto. Define la cantidad real y cómo lo revelan.
                    <div class="example">📝 "Tabaco: 2 paquetes/día | Revela: si preguntan | Alcohol: 3-4 carajillos/día + vino | Revela: miente → 'Lo normal, lo que bebe cualquier español'"</div>
                </span>
            </span>
        </label>
        <div class="dynamic-list" id="vicesContainer"></div>
        <button type="button" class="btn-add-item" onclick="addVice()">
            <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            Añadir Vicio
        </button>
    </div>

    {{-- Antecedentes familiares --}}
    <div class="form-group">
        <label for="family_history">
            Antecedentes Familiares <span class="hint">(opcional)</span>
            <span class="help-tooltip">
                <span class="help-tooltip-icon">?</span>
                <span class="help-tooltip-bubble">
                    <strong>¿Para qué sirve?</strong>
                    Historial médico de familiares directos. Clave para evaluar riesgo genético.
                    <div class="example">📝 "Padre: murió del corazón a los 52 (no le gusta hablar de esto). Madre: diabetes tipo 2."</div>
                </span>
            </span>
        </label>
        <textarea id="family_history" name="family_history" placeholder="Ej: Padre fallecido de IAM a los 52. Madre con diabetes tipo 2.">{{ old('family_history') }}</textarea>
    </div>

    {{-- Entorno familiar --}}
    <div class="form-group">
        <label for="family_environment">
            Entorno Familiar y Social <span class="hint">(opcional)</span>
            <span class="help-tooltip">
                <span class="help-tooltip-icon">?</span>
                <span class="help-tooltip-bubble">
                    <strong>¿Para qué sirve?</strong>
                    Con quién vive, situación económica, red de apoyo. Afecta a adherencia, salud mental y planificación del alta.
                    <div class="example">📝 "Casado con Lourdes. Dos hijos adultos fuera. Ahogado económicamente. Vive del camión."</div>
                    <div class="example">📝 "Segundo hijo, hermano de 3 años con catarro. Embarazada del tercero. Casa a una hora del hospital."</div>
                </span>
            </span>
        </label>
        <textarea id="family_environment" name="family_environment"
                  placeholder="Ej: Casado con Lourdes. Dos hijos adultos fuera de casa. Situación económica difícil.">{{ old('family_environment') }}</textarea>
    </div>

    {{-- Diagnóstico real --}}
    <div class="form-group">
        <label for="real_diagnosis">
            Diagnóstico Real <span class="required">*</span>
            <span class="help-tooltip">
                <span class="help-tooltip-icon">?</span>
                <span class="help-tooltip-bubble">
                    <strong>¿Para qué sirve?</strong>
                    La verdad médica que el paciente NO sabe. Se le da a la IA como contexto para mantener coherencia.
                    <div class="example">📝 "Insuficiencia cardíaca congestiva no diagnosticada"</div>
                    <div class="example">📝 "Simulación. No tiene patología real."</div>
                </span>
            </span>
        </label>
        <input type="text" id="real_diagnosis" name="real_diagnosis" value="{{ old('real_diagnosis') }}"
               placeholder="Ej: Insuficiencia cardíaca congestiva no diagnosticada" required>
    </div>

    {{-- Hallazgos clave --}}
    <div class="form-group">
        <label for="key_findings">
            Hallazgos Clave para el Diagnóstico <span class="hint">(opcional)</span>
            <span class="help-tooltip">
                <span class="help-tooltip-icon">?</span>
                <span class="help-tooltip-bubble">
                    <strong>¿Para qué sirve?</strong>
                    Las pistas cruciales que el estudiante debería identificar para llegar al diagnóstico.
                    <div class="example">📝 "Presión en pecho conduciendo, disnea paroxística nocturna, edemas maleolares, incumplimiento terapéutico grave"</div>
                </span>
            </span>
        </label>
        <textarea id="key_findings" name="key_findings"
                  placeholder="Ej: Presión en pecho, disnea nocturna, edemas, incumplimiento terapéutico">{{ old('key_findings') }}</textarea>
    </div>
</div>