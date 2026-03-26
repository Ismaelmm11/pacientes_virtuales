<?php

namespace App\Services;

use App\Models\Patient;
use App\Models\PatientKnowledgeBase;
use App\Models\PatientRoleIdentity;
use App\Models\PatientPsychology;
use App\Models\PatientConversationLogic;
use App\Models\PatientPrompt;
use App\Services\PromptGeneratorService;
use Illuminate\Support\Facades\DB;

/**
 * Servicio para gestionar la lógica de negocio de los Pacientes Virtuales.
 *
 * Centraliza la creación y edición completa de un paciente, rellenando TODAS
 * las tablas intermedias (identity, psychology, knowledge_base, conversation_logic)
 * y generando el prompt final en patient_prompts.
 *
 * FLUJO CREACIÓN:
 * Formulario → StorePatientRequest → PatientController → createPatient() → 5 tablas → Prompt
 *
 * FLUJO EDICIÓN:
 * Formulario → UpdatePatientRequest → PatientController → updatePatient() → 5 tablas → Prompt
 *
 * TABLAS QUE GESTIONA:
 * 1. patients                     — Registro principal
 * 2. patient_role_identity        — Quién es, acompañante, contexto
 * 3. patient_psychology           — Personalidad, comunicación, reglas
 * 4. patient_knowledge_base       — Síntomas, antecedentes, diagnóstico
 * 5. patient_conversation_logic   — Revelación, gatillos, contradicciones
 * 6. patient_prompts              — Prompt Markdown generado
 */
class PatientService
{
    protected PromptGeneratorService $promptGenerator;

    public function __construct(PromptGeneratorService $promptGenerator)
    {
        $this->promptGenerator = $promptGenerator;
    }

    /* =================================================================
     * MÉTODOS PÚBLICOS PRINCIPALES
     * ================================================================= */

    /**
     * Crea un paciente completo con todos sus datos asociados.
     * Todo se ejecuta dentro de una transacción DB.
     */
    public function createPatient(array $data, int $userId): Patient
    {
        return DB::transaction(function () use ($data, $userId) {

            $mode = $data['mode'] ?? 'basic';

            if ($mode === 'basic') {
                $data['frase_inicial'] = $data['frase_inicial']
                    ?? $data['chief_complaint']
                    ?? 'Hola doctor, vengo porque no me encuentro bien.';
                $data['attendee_type'] = $data['attendee_type'] ?? 'patient';
            }

            $patient = Patient::create([
                'case_title' => $data['case_title'],
                'patient_description' => $data['patient_description'] ?? null,
                'created_by_user_id' => $userId,
                'subject_id' => $data['subject_id'],
                'mode' => $mode,
                'learning_objectives' => $data['learning_objectives'] ?? null,
                'puede_inventar_datos_medicos' => $data['puede_inventar_datos_medicos'] ?? false,
                'initial_message' => $data['frase_inicial'],
            ]);

            $this->createRoleIdentity($patient, $data);
            $this->createPsychology($patient, $data, $mode);
            $this->createKnowledgeBase($patient, $data, $mode);
            $this->createConversationLogic($patient, $data, $mode);
            $this->createCoherenceExample($patient, $data);
            $this->generateAndStorePrompt($patient, $data);

            return $patient;
        });
    }

    /**
     * Actualiza un paciente existente y regenera el prompt.
     * No modifica: created_by_user_id, mode, subject_id (no cambian en edición).
     */
    public function updatePatient(array $data, Patient $patient): void
    {
        DB::transaction(function () use ($data, $patient) {

            $mode = $patient->mode;

            if ($mode === 'basic') {
                $data['frase_inicial'] = $data['frase_inicial']
                    ?? $patient->initial_message;
                $data['attendee_type'] = $data['attendee_type'] ?? 'patient';
            }

            // 1. Tabla principal
            $patient->update([
                'case_title' => $data['case_title'],
                'patient_description' => $data['patient_description'] ?? null,
                'subject_id' => $data['subject_id'],
                'learning_objectives' => $data['learning_objectives'] ?? null,
                'puede_inventar_datos_medicos' => $data['puede_inventar_datos_medicos'] ?? false,
                'initial_message' => $data['frase_inicial'],
            ]);

            // 2-5. Tablas relacionadas — reutiliza los mismos builders que createPatient
            $patient->identity()->update($this->buildIdentityData($data));
            $patient->psychology()->update($this->buildPsychologyData($data, $mode));
            $patient->knowledgeBase()->update($this->buildKnowledgeBaseData($data, $mode));
            $patient->conversationLogic()->update($this->buildConversationLogicData($data, $mode));

            // 6. Ejemplos de coherencia: eliminar y recrear
            $patient->coherenceExamples()->delete();
            $this->createCoherenceExample($patient, $data);

            // 7. Regenerar prompt
            $patient->load(['identity', 'psychology', 'knowledgeBase', 'conversationLogic', 'coherenceExamples']);
            $promptContent = $this->promptGenerator->generate($patient);
            $patient->prompt()->updateOrCreate(
                [],
                [
                    'subtitulo' => $data['case_title'],
                    'prompt_content' => $promptContent,
                    'generated_at' => now(),
                    'is_manually_edited' => false,
                ]
            );
        });
    }

    /**
     * Extrae los datos del paciente en formato de campos de formulario.
     * Se usa para pre-rellenar el formulario de edición via session()->flashInput().
     *
     * Nota: algunos campos transformados (patient_name, patient_age, patient_gender)
     * se recuperan parseando el campo datos_demograficos generado al crear.
     */
    public function extractFormData(Patient $patient): array
    {
        $identity = $patient->identity;
        $psychology = $patient->psychology;
        $kb = $patient->knowledgeBase;
        $logic = $patient->conversationLogic;
        $coherence = $patient->coherenceExamples->first();
        $comm = $psychology?->caracteristicas_comunicacion ?? [];

        $antecedentes = $kb?->antecedentes_medicos ?? [];

        $data = [
            // Tabla patients
            'case_title' => $patient->case_title,
            'patient_description' => $patient->patient_description,
            'learning_objectives' => $patient->learning_objectives,
            'mode' => $patient->mode,
            'subject_id' => $patient->subject_id,
            'puede_inventar_datos_medicos' => $patient->puede_inventar_datos_medicos ? '1' : '0',
            'frase_inicial' => $patient->initial_message,

            // Identidad
            'attendee_type' => $identity?->es_acompanante ? 'companion' : 'patient',
            'companion_name' => $identity?->nombre_acompanante,
            'companion_relation' => $identity?->relacion_con_paciente,
            'companion_age' => $identity?->edad_acompanante,
            'companion_gender' => $identity?->genero_acompanante,
            'patient_name' => $identity?->patient_name,
            'patient_age' => $identity?->patient_age,
            'patient_gender' => $identity?->patient_gender,
            'occupation' => $identity?->occupation,
            'personal_context' => $identity?->personal_context,
            'education_level' => $identity?->education_level,

            // Psicología
            'personality_type' => $comm['personalidad'] ?? null,
            'verbosity_level' => $comm['nivel_verbosidad'] ?? 3,
            'medical_knowledge' => $comm['nivel_conocimiento'] ?? 2,
            'hidden_concerns' => $psychology?->preocupaciones_ocultas,
            'conflicto_interno' => $psychology?->conflicto_interno,
            'personality_custom' => $psychology?->estado_emocional_frase,
            'verbosity_custom' => $comm['descripcion_verbosidad'] ?? null,
            'knowledge_custom' => $comm['descripcion_conocimiento'] ?? null,

            // Conocimiento
            'real_diagnosis' => $kb?->diagnostico_real,
            'key_findings' => $kb?->hallazgos_clave,
            'motivo_consulta' => $kb?->motivo_consulta,
            'medical_history' => $antecedentes['texto_libre'] ?? null,
            'family_history' => $kb?->historia_familiar['texto'] ?? null,
            'symptoms' => $this->reverseSymptoms($kb?->sintomas_asociados ?? []),
            'medications' => $this->reverseMedications($kb?->medicacion_tomada ?? []),
            'vices' => $this->reverseVices($kb?->vicios ?? []),

            // Conversación
            'special_instructions' => $logic?->instrucciones_especiales,
            'frases_limite' => $logic?->frases_limite ?? [],

            // Ejemplo de coherencia
            'ejemplo_coherencia' => $coherence ? [
                'pregunta' => $coherence->question,
                'coherente' => $coherence->correct_answer,
                'incoherente' => $coherence->incorrect_answer,
            ] : null,
        ];

        // Eliminar nulos para no sobreescribir old() con vacíos
        return array_filter($data, fn($v) => $v !== null);
    }

    /* =================================================================
     * BUILDERS — Construyen los arrays de atributos para cada tabla.
     * Usados tanto en create como en update.
     * ================================================================= */

    private function buildIdentityData(array $data): array
    {
        $name = $data['patient_name'];
        $age = $data['patient_age'];
        $gender = $data['patient_gender'];
        $isCompanion = ($data['attendee_type'] ?? 'patient') === 'companion';

        $genderText = match ($gender) {
            'masculino' => 'hombre',
            'femenino' => 'mujer',
            default => 'persona',
        };

        if ($isCompanion) {
            $companionName = $data['companion_name'];
            $relation = $this->formatRelation($data['companion_relation'] ?? 'otro');
            $rolPrincipal = "Eres {$companionName}, {$relation} de {$name} ({$age} años, {$genderText}). "
                . "Has traído a {$name} a la consulta y hablas en su nombre. "
                . "El paciente NO interviene en la conversación; tú eres quien habla con el médico.";
        } else {
            $rolPrincipal = "Eres {$name}, un paciente de {$age} años ({$genderText}) "
                . "que acude a una consulta médica.";
        }

        $datosDemograficos = "{$name}, {$age} años, {$genderText}.";
        if (!empty($data['education_level'])) {
            $datosDemograficos .= " Nivel educativo: " . $this->formatEducationLevel($data['education_level']) . ".";
        }

        $contexto = '';
        if (!empty($data['occupation'])) {
            $contexto = $data['occupation'];
        }
        if (!empty($data['personal_context'])) {
            $contexto .= ($contexto ? '. ' : '') . $data['personal_context'];
        }

        return [
            // Al final del array, junto al resto de campos:
            'patient_name' => $data['patient_name'],
            'patient_age' => $data['patient_age'],
            'patient_gender' => $data['patient_gender'],
            'occupation' => $data['occupation'] ?? null,
            'personal_context' => $data['personal_context'] ?? null,
            'education_level' => $data['education_level'] ?? null,

            'es_acompanante' => $isCompanion,
            'nombre_acompanante' => $isCompanion ? ($data['companion_name'] ?? null) : null,
            'relacion_con_paciente' => $isCompanion ? ($data['companion_relation'] ?? null) : null,
            'edad_acompanante' => $isCompanion ? ($data['companion_age'] ?? null) : null,
            'genero_acompanante' => $isCompanion ? ($data['companion_gender'] ?? null) : null,
            'rol_principal' => $rolPrincipal,
            'datos_demograficos' => $datosDemograficos,
            'contexto_sociolaboral' => $contexto ?: 'Sin contexto adicional.',
            'nivel_conocimiento' => $this->formatMedicalKnowledge($data['medical_knowledge'] ?? 2),
            'campos_custom' => null,
        ];
    }

    private function buildPsychologyData(array $data, string $mode): array
    {
        $personality = $data['personality_type'] ?? 'colaborador';
        $verbosity = (int) ($data['verbosity_level'] ?? 3);
        $emotionalDefaults = $this->getEmotionalDefaults($personality);

        $estadoFrase = !empty($data['personality_custom'])
            ? $data['personality_custom']
            : $emotionalDefaults['frase'];

        $estadoContexto = $data['emotional_context'] ?? $emotionalDefaults['contexto'];
        $conflictoInterno = ($mode === 'advanced') ? ($data['conflicto_interno'] ?? null) : null;
        $comunicacion = $this->buildCommunicationTraits($personality, $verbosity, $data);

        $reglasInteraccion = null;
        if ($mode === 'advanced' && !empty($data['rules'])) {
            $reglasInteraccion = array_map(fn($rule) => [
                'condicion' => $rule['condition'] ?? '',
                'reaccion' => $rule['reaction'] ?? '',
            ], $data['rules']);
        }

        return [
            'estado_emocional_frase' => $estadoFrase,
            'estado_emocional_contexto' => $estadoContexto,
            'conflicto_interno' => $conflictoInterno,
            'caracteristicas_comunicacion' => $comunicacion,
            'reglas_interaccion' => $reglasInteraccion,
            'preocupaciones_ocultas' => $data['hidden_concerns'] ?? null,
        ];
    }

    private function buildKnowledgeBaseData(array $data, string $mode): array
    {
        $isAdvanced = $mode === 'advanced';

        $antecedentes = $isAdvanced
            ? [
                'enfermedades' => $this->formatDynamicListWithReveal($data['diseases'] ?? []),
                'cirugias' => $this->formatDynamicListWithReveal($data['surgeries'] ?? []),
                'alergias' => $data['allergies'] ?? null,
            ]
            : ['texto_libre' => $data['medical_history'] ?? null];

        $medicacion = $isAdvanced
            ? $this->formatMedications($data['medications'] ?? [])
            : $this->formatBasicMedications($data['medications'] ?? []);

        $sintomas = $this->formatSymptoms($data['symptoms'] ?? [], $isAdvanced);

        $vicios = !empty($data['vices'])
            ? $this->formatDynamicListWithReveal($data['vices'] ?? [], 'frequency')
            : [];

        $historiaNarrativa = $data['historia_narrativa'] ?? $this->buildNarrative($data, $mode);

        $historiaFamiliar = !empty($data['family_history'])
            ? ['texto' => $data['family_history']]
            : [];

        $entornoFamiliar = !empty($data['personal_context'])
            ? ['texto' => $data['personal_context']]
            : [];

        return [
            'frase_inicial' => $data['frase_inicial'],
            'motivo_consulta' => $data['motivo_consulta'] ?? null,
            'historia_narrativa' => $historiaNarrativa,
            'diagnostico_real' => $data['real_diagnosis'],
            'hallazgos_clave' => $data['key_findings'] ?? null,
            'antecedentes_medicos' => $antecedentes,
            'medicacion_tomada' => $medicacion,
            'sintomas_asociados' => $sintomas,
            'historia_familiar' => $historiaFamiliar,
            'entorno_familiar' => $entornoFamiliar,
            'hobbies' => [],
            'vicios' => $vicios,
        ];
    }

    private function buildConversationLogicData(array $data, string $mode): array
    {
        $isAdvanced = $mode === 'advanced';
        $revelacionSintomas = $this->buildSymptomRevealRules($data['symptoms'] ?? []);

        $gatillos = null;
        if ($isAdvanced && !empty($data['triggers'])) {
            $gatillos = array_map(fn($t) => [
                'tema' => $t['topic'] ?? '',
                'reaccion' => $t['reaction'] ?? '',
            ], $data['triggers']);
        }

        $contradicciones = null;
        if ($isAdvanced && !empty($data['contradictions'])) {
            $contradicciones = array_map(fn($c) => [
                'que_dice' => $c['says'] ?? '',
                'que_contradice' => $c['contradicts'] ?? '',
                'si_le_pillan' => $c['caught'] ?? '',
            ], $data['contradictions']);
        }

        $eventosCierre = ['despedida_natural' => 'El paciente se despide cuando siente que la consulta ha terminado.'];
        if ($isAdvanced && !empty($data['closures'])) {
            $eventosCierre = array_map(fn($c) => [
                'condicion' => $c['condition'] ?? '',
                'accion' => $c['action'] ?? '',
            ], $data['closures']);
        }

        $frasesLimite = null;
        if (!empty($data['frases_limite'])) {
            $frasesLimite = array_values(
                array_filter($data['frases_limite'], fn($f) => !empty(trim($f)))
            );
        }

        return [
            'revelacion_sintomas' => $revelacionSintomas,
            'gatillos_emocionales' => $gatillos,
            'contradicciones' => $contradicciones,
            'interacciones_trigger' => null,
            'eventos_cierre' => $eventosCierre,
            'instrucciones_especiales' => $data['special_instructions'] ?? null,
            'frases_limite' => $frasesLimite,
        ];
    }

    /* =================================================================
     * PERSISTENCIA — Llaman a los builders y guardan en BD.
     * ================================================================= */

    private function createRoleIdentity(Patient $patient, array $data): PatientRoleIdentity
    {
        return PatientRoleIdentity::create([
            'patient_id' => $patient->id,
            ...$this->buildIdentityData($data),
        ]);
    }

    private function createPsychology(Patient $patient, array $data, string $mode): PatientPsychology
    {
        return PatientPsychology::create([
            'patient_id' => $patient->id,
            ...$this->buildPsychologyData($data, $mode),
        ]);
    }

    private function createKnowledgeBase(Patient $patient, array $data, string $mode): PatientKnowledgeBase
    {
        return PatientKnowledgeBase::create([
            'patient_id' => $patient->id,
            ...$this->buildKnowledgeBaseData($data, $mode),
        ]);
    }

    private function createConversationLogic(Patient $patient, array $data, string $mode): PatientConversationLogic
    {
        return PatientConversationLogic::create([
            'patient_id' => $patient->id,
            ...$this->buildConversationLogicData($data, $mode),
        ]);
    }

    private function createCoherenceExample(Patient $patient, array $data): void
    {
        $ec = $data['ejemplo_coherencia'] ?? [];
        if (empty($ec['pregunta']) || empty($ec['coherente']) || empty($ec['incoherente'])) {
            return;
        }

        \App\Models\CoherenceExample::create([
            'patient_id' => $patient->id,
            'question' => $ec['pregunta'],
            'correct_answer' => $ec['coherente'],
            'incorrect_answer' => $ec['incoherente'],
            'example_order' => 1,
        ]);
    }

    /* =================================================================
     * GENERACIÓN DEL PROMPT
     * ================================================================= */

    private function generateAndStorePrompt(Patient $patient, array $data): PatientPrompt
    {
        $patient->load(['identity', 'psychology', 'knowledgeBase', 'conversationLogic', 'coherenceExamples']);

        $promptContent = $this->promptGenerator->generate($patient);

        return PatientPrompt::create([
            'patient_id' => $patient->id,
            'subtitulo' => $data['case_title'],
            'prompt_content' => $promptContent,
            'generated_at' => now(),
            'version' => 1,
            'is_manually_edited' => false,
        ]);
    }

    /* =================================================================
     * HELPERS: REVERSIÓN (extractFormData)
     * ================================================================= */

    /**
     * Convierte el array JSON de síntomas de vuelta a formato de formulario.
     */
    private function reverseSymptoms(array $symptoms): array
    {
        return array_map(fn($s) => [
            'name' => $s['nombre'] ?? '',
            'reveal' => $s['revelacion'] ?? 'espontaneo',
            'lie_text' => $s['mentira'] ?? '',
            'intensity' => $s['intensidad'] ?? '',
            'aggravating' => $s['agravantes'] ?? '',
            'relieving' => $s['atenuantes'] ?? '',
        ], $symptoms);
    }

    /**
     * Convierte el array JSON de medicación de vuelta a formato de formulario.
     */
    private function reverseMedications(array $meds): array
    {
        return array_map(fn($m) => [
            'name' => $m['nombre'] ?? '',
            'frequency' => $m['frecuencia'] ?? '',
            'dose' => $m['dosis'] ?? '',
            'adherence' => $m['adherencia'] ?? false,
            'adherence_detail' => $m['detalle_adherencia'] ?? '',
            'reveal' => $m['revelacion'] ?? 'espontaneo',
            'lie_text' => $m['mentira'] ?? '',
        ], $meds);
    }


    private function reverseVices(array $vicios): array
    {
        return array_map(fn($v) => [
            'name' => $v['nombre'] ?? '',
            'reveal' => $v['revelacion'] ?? 'espontaneo',
            'lie_text' => $v['mentira'] ?? '',
        ], $vicios);
    }

    /* =================================================================
     * HELPERS: FORMATEO DE ARRAYS DINÁMICOS
     * ================================================================= */

    private function formatSymptoms(array $symptoms, bool $isAdvanced): array
    {
        $formatted = [];
        foreach ($symptoms as $s) {
            if (empty($s['name']))
                continue;

            $item = [
                'nombre' => $s['name'],
                'revelacion' => $s['reveal'] ?? 'espontaneo',
            ];

            if (($s['reveal'] ?? '') === 'miente') {
                $item['mentira'] = $s['lie_text'] ?? null;
            }

            if ($isAdvanced) {
                $item['intensidad'] = $s['intensity'] ?? null;
                $item['agravantes'] = $s['aggravating'] ?? null;
                $item['atenuantes'] = $s['relieving'] ?? null;
            }

            $formatted[] = $item;
        }
        return $formatted;
    }

    private function formatDynamicListWithReveal(array $items, string $extraField = 'since'): array
    {
        $formatted = [];
        foreach ($items as $item) {
            if (empty($item['name']))
                continue;

            $entry = [
                'nombre' => $item['name'],
                'revelacion' => $item['reveal'] ?? 'espontaneo',
            ];

            if (!empty($item[$extraField])) {
                $entry[$extraField] = $item[$extraField];
            }

            if (($item['reveal'] ?? '') === 'miente') {
                $entry['mentira'] = $item['lie_text'] ?? null;
            }

            $formatted[] = $entry;
        }
        return $formatted;
    }

    private function formatMedications(array $medications): array
    {
        $formatted = [];
        foreach ($medications as $med) {
            if (empty($med['name']))
                continue;

            $entry = [
                'nombre' => $med['name'],
                'dosis' => $med['dose'] ?? null,
                'frecuencia' => $med['frequency'] ?? null,
                'adherencia' => ($med['adherence'] ?? '') === 'total',
                'revelacion' => $med['reveal'] ?? 'espontaneo',
            ];

            if (!empty($med['adherence_detail'])) {
                $entry['detalle_adherencia'] = $med['adherence_detail'];
            }

            if (($med['reveal'] ?? '') === 'miente') {
                $entry['mentira'] = $med['lie_text'] ?? null;
            }

            $formatted[] = $entry;
        }
        return $formatted;
    }

    private function formatBasicMedications(array $medications): array
    {
        $formatted = [];
        foreach ($medications as $med) {
            if (empty($med['name']))
                continue;
            $formatted[] = [
                'nombre' => $med['name'],
                'frecuencia' => $med['frequency'] ?? null,
            ];
        }
        return $formatted;
    }

    private function buildSymptomRevealRules(array $symptoms): ?array
    {
        $rules = [];
        foreach ($symptoms as $s) {
            if (empty($s['name']))
                continue;

            $rule = [
                'sintoma' => $s['name'],
                'revelacion' => $s['reveal'] ?? 'espontaneo',
            ];

            if (($s['reveal'] ?? '') === 'miente') {
                $rule['mentira'] = $s['lie_text'] ?? null;
            }

            $rules[] = $rule;
        }

        return !empty($rules) ? $rules : null;
    }

    private function buildNarrative(array $data, string $mode): string
    {
        $parts = [];

        if ($mode === 'basic') {
            if (!empty($data['medical_history'])) {
                $parts[] = "Antecedentes relevantes: " . $data['medical_history'] . ".";
            }
            if (!empty($data['personal_context'])) {
                $parts[] = "Contexto personal: " . $data['personal_context'] . ".";
            }
        }

        return !empty($parts) ? implode(' ', $parts) : '';
    }

    /* =================================================================
     * HELPERS: FORMATEO DE VALORES
     * ================================================================= */

    private function getEmotionalDefaults(string $personality): array
    {
        $defaults = [
            'colaborador' => [
                'frase' => 'Estás tranquilo/a y dispuesto/a a colaborar con el médico. Confías en el sistema sanitario y vienes con buena disposición. Respondes a las preguntas de forma abierta y honesta.',
                'contexto' => 'Confías en los profesionales sanitarios y vienes con buena disposición.',
            ],
            'ansioso' => [
                'frase' => 'Estás visiblemente nervioso/a y preocupado/a. La incertidumbre sobre tu salud te genera mucha ansiedad. Tiendes a hacer muchas preguntas y a pedir confirmación constante de que no es nada grave.',
                'contexto' => 'La incertidumbre sobre tu salud te genera mucha ansiedad.',
            ],
            'reservado' => [
                'frase' => 'Eres reservado/a y te cuesta abrirte al médico. No te sientes cómodo/a hablando de temas personales con desconocidos. Das respuestas cortas y hay que insistir para que des detalles.',
                'contexto' => 'No te sientes cómodo/a hablando de temas personales con desconocidos.',
            ],
            'demandante' => [
                'frase' => 'Estás impaciente y esperas respuestas inmediatas. Sientes que llevas demasiado tiempo con este problema sin solución. Interrumpes con frecuencia y cuestionas las decisiones del médico.',
                'contexto' => 'Sientes que el sistema sanitario te ha hecho esperar demasiado.',
            ],
            'minimizador' => [
                'frase' => 'Le quitas importancia a tus síntomas. No quieres parecer exagerado/a ni hacer perder el tiempo al médico. Dices cosas como "seguro que no es nada" o "no sé ni por qué he venido".',
                'contexto' => 'No quieres parecer exagerado/a ni hacer perder el tiempo al médico.',
            ],
            'hipocondriaco' => [
                'frase' => 'Estás muy asustado/a y convencido/a de que tienes algo grave. Cada síntoma te parece señal de una enfermedad seria. Has buscado tus síntomas en internet y estás convencido/a del peor escenario.',
                'contexto' => 'Has buscado tus síntomas en internet y estás convencido/a del peor diagnóstico posible.',
            ],
            'agresivo' => [
                'frase' => 'Estás enfadado/a y a la defensiva. Sientes que nadie te toma en serio o que el sistema sanitario te ha fallado. Respondes de forma cortante y puedes llegar a levantar la voz si te sientes cuestionado/a.',
                'contexto' => 'Sientes que nadie te toma en serio y que el sistema te ha fallado.',
            ],
            'deprimido' => [
                'frase' => 'Estás apático/a y sin energía. Hablas en voz baja y con desgana. Te cuesta expresar lo que sientes porque "da igual". Puedes mostrar desinterés por el tratamiento o la recuperación.',
                'contexto' => 'Estás en un momento vital muy bajo y sientes que nada tiene solución.',
            ],
            'desconfiado' => [
                'frase' => 'No confías en los médicos ni en el sistema sanitario. Cuestionas todo lo que te dicen y pides segundas opiniones. Puedes mencionar remedios caseros o tratamientos alternativos como mejor opción.',
                'contexto' => 'Has tenido malas experiencias previas con médicos o el sistema sanitario.',
            ],
            'confuso' => [
                'frase' => 'Estás desorientado/a y te cuesta seguir la conversación. Puedes contradecirte sobre fechas o detalles. Necesitas que te repitan las cosas y a veces respondes a una pregunta diferente de la que te han hecho.',
                'contexto' => 'Te cuesta concentrarte y seguir el hilo de la conversación.',
            ],
            'evasivo' => [
                'frase' => 'Evitas responder a ciertas preguntas o cambias de tema cuando la conversación se acerca a algo incómodo. Das rodeos y divagaciones en vez de respuestas directas. Hay algo que no quieres contar.',
                'contexto' => 'Hay algo que no quieres que el médico descubra.',
            ],
        ];

        return $defaults[$personality] ?? $defaults['colaborador'];
    }

    private function buildCommunicationTraits(string $personality, int $verbosity, array $data = []): array
    {
        return [
            'personalidad' => $personality,
            'nivel_verbosidad' => $verbosity,
            'descripcion_verbosidad' => !empty($data['verbosity_custom'])
                ? $data['verbosity_custom']
                : $this->formatVerbosity($verbosity),
            'nivel_conocimiento' => (int) ($data['medical_knowledge'] ?? 2),
            'descripcion_conocimiento' => !empty($data['knowledge_custom'])
                ? $data['knowledge_custom']
                : $this->formatMedicalKnowledge((int) ($data['medical_knowledge'] ?? 2)),
        ];
    }

    private function formatVerbosity(int $level): string
    {
        $labels = [
            1 => 'Muy escueto: respuestas de pocas palabras, hay que sacarle la información con insistencia.',
            2 => 'Escueto: respuestas cortas y directas. No ofrece detalles voluntariamente.',
            3 => 'Normal: respuestas de longitud media. Da información relevante cuando se le pregunta.',
            4 => 'Detallista: da bastante contexto y detalles sin que se lo pidan.',
            5 => 'Muy detallista: tiende a extenderse, divagar y contar historias tangenciales.',
        ];
        return $labels[$level] ?? $labels[3];
    }

    private function formatMedicalKnowledge(int $level): string
    {
        $labels = [
            1 => 'Ningún conocimiento médico. Usa términos muy coloquiales: "me duele aquí", "tengo la tripa revuelta", "se me duerme el brazo".',
            2 => 'Conocimiento mínimo. Sabe lo básico que ha oído: "creo que es la tensión", "me dijeron algo de azúcar en la sangre". Confunde términos.',
            3 => 'Conocimiento básico. Entiende términos comunes: "tengo hipertensión", "me recetaron antiinflamatorios". Describe síntomas con cierta precisión.',
            4 => 'Conocimiento moderado. Ha leído sobre su condición: "creo que podría ser una ciática", "me hicieron una analítica y tenía el colesterol LDL alto".',
            5 => 'Profesional sanitario o con formación médica. Usa terminología técnica correctamente: "tengo dolor precordial opresivo con irradiación a MSI", "sospecho un SCA".',
        ];
        return $labels[$level] ?? $labels[2];
    }

    private function formatEducationLevel(string $level): string
    {
        $levels = [
            'sin_estudios' => 'Sin estudios formales',
            'primaria' => 'Educación primaria',
            'secundaria' => 'Educación secundaria',
            'bachillerato' => 'Bachillerato',
            'universitario' => 'Universitario',
            'postgrado' => 'Postgrado',
        ];
        return $levels[$level] ?? $level;
    }

    private function formatRelation(string $relation): string
    {
        $relations = [
            'madre' => 'la madre',
            'padre' => 'el padre',
            'hijo_a' => 'el/la hijo/a',
            'pareja' => 'la pareja',
            'amigo_a' => 'un/a amigo/a',
            'cuidador_a' => 'el/la cuidador/a',
            'otro' => 'un familiar',
        ];
        return $relations[$relation] ?? 'un familiar';
    }
}
