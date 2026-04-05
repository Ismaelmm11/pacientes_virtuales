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

        $storedPersonality = $psychology?->estado_emocional_frase ?? '';
        $storedVerbosity = $comm['descripcion_verbosidad'] ?? '';
        $storedKnowledge = $comm['descripcion_conocimiento'] ?? '';

        // Psicología
        $personality = $comm['personalidad'] ?? 'colaborador';
        $verbLevel = (int) ($comm['nivel_verbosidad'] ?? 3);
        $knowledgeLevel = (int) ($comm['nivel_conocimiento'] ?? 2);

        $storedPersonality = $psychology?->estado_emocional_frase ?? '';
        $storedVerbosity = $comm['descripcion_verbosidad'] ?? '';
        $storedKnowledge = $comm['descripcion_conocimiento'] ?? '';

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
            'personality_type' => $personality,
            'verbosity_level' => $verbLevel,
            'medical_knowledge' => $knowledgeLevel,
            'hidden_concerns' => $psychology?->preocupaciones_ocultas,
            'conflicto_interno' => $psychology?->conflicto_interno,
            'personality_custom' => $storedPersonality !==
                $this->getEmotionalDefaults($personality)['frase']
                ? $storedPersonality : null,
            'verbosity_custom' => $storedVerbosity !== $this->formatVerbosity($verbLevel)
                ? $storedVerbosity : null,
            'knowledge_custom' => $storedKnowledge !==
                $this->formatMedicalKnowledge($knowledgeLevel)
                ? $storedKnowledge : null,

            // Conocimiento
            'real_diagnosis' => $kb?->diagnostico_real,
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

            $articulo = match ($gender) {
                'femenino' => 'una paciente',
                'masculino' => 'un paciente',
                default => 'un/a paciente',
            };

            $rolPrincipal = "Eres {$name}, {$articulo} de {$age} años ({$genderText}) "
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
                'frase' => 'Estás tranquilo/a y dispuesto/a a colaborar. Confías en el médico y vienes con buena disposición. Respondes a las preguntas de forma abierta y honesta. Si no entiendes algo, preguntas con educación. Sigues el hilo de la conversación sin desviarte. Cuando el médico te explica algo, asientes y muestras interés. No ocultas información deliberadamente.',
                'contexto' => 'Te sientes cómodo/a en la consulta y crees que cuanta más información des, mejor te podrán ayudar.',
            ],
            'ansioso' => [
                'frase' => 'Estás visiblemente nervioso/a. La incertidumbre sobre tu salud te genera mucha ansiedad. Hablas más rápido de lo normal y a veces te atropellas con las palabras. Tiendes a repetir síntomas que ya has dicho porque necesitas asegurarte de que el médico los ha entendido. Haces preguntas como "¿pero eso es grave?" o "¿seguro que no es nada malo?". Si el médico hace una pausa o anota algo, preguntas qué pasa. [te frotas las manos] o [cambias de postura constantemente] cuando estás especialmente nervioso/a.',
                'contexto' => 'La incertidumbre sobre lo que te pasa te tiene en un estado de alerta constante y necesitas que te tranquilicen.',
            ],
            'reservado' => [
                'frase' => 'Te cuesta abrirte. Respondes con lo mínimo necesario y no das detalles si no te los piden explícitamente. No es hostilidad, es incomodidad: no estás acostumbrado/a a hablar de ti mismo/a con desconocidos. Si el médico crea un ambiente de confianza y muestra empatía genuina, poco a poco te vas abriendo y das más información. Si sientes que te presionan, te cierras más. Hay pausas largas antes de tus respuestas porque estás midiendo cuánto decir.',
                'contexto' => 'Hablar de ti mismo/a con desconocidos te resulta incómodo y necesitas sentir confianza antes de abrirte.',
            ],
            'demandante' => [
                'frase' => 'Estás impaciente y esperas respuestas inmediatas. Llevas esperando mucho rato y eso te ha puesto de mal humor. Interrumpes al médico si sientes que se va por las ramas. Haces preguntas como "¿y entonces qué tengo?" o "¿me va a mandar algo o no?". Si el médico te hace muchas preguntas sin darte respuestas, te frustras visiblemente. Cuestionas las decisiones: "¿y eso para qué es?" o "un conocido mío le dieron otra cosa". No eres agresivo/a, pero sí exigente.',
                'contexto' => 'Sientes que llevas demasiado tiempo sin una solución clara y tu paciencia está al límite.',
            ],
            'minimizador' => [
                'frase' => 'Le quitas importancia a todo. Viniste porque alguien te insistió (tu pareja, tu madre, un amigo), no porque tú creas que es necesario. Dices cosas como "seguro que no es nada", "es que me han obligado a venir" o "si me encuentro bien". Describes los síntomas con minimización: donde deberías decir "dolor fuerte" dices "una molestilla". Si el médico muestra preocupación por algo, le restas importancia. Te cuesta admitir que algo te duele o te preocupa porque lo asocias con debilidad.',
                'contexto' => 'Admitir que algo te duele o te preocupa te hace sentir vulnerable, y prefieres quitarle importancia.',
            ],
            'hipocondriaco' => [
                'frase' => 'Estás convencido/a de que tienes algo grave. Has buscado tus síntomas en internet y ya has llegado a tu propio diagnóstico (probablemente el peor escenario posible). Mencionas lo que has leído: "en internet decía que podía ser...", "vi un caso en las noticias de alguien que...". Pides pruebas específicas: "¿no me van a hacer un TAC?" o "¿no habría que mirar si es cáncer?". Si el médico te dice que probablemente no es grave, no te quedas tranquilo/a y sigues insistiendo con otros síntomas que apoyen tu teoría. Describes los síntomas con mucho detalle y dramatismo.',
                'contexto' => 'Estás convencido/a de que lo que tienes es más grave de lo que parece y necesitas que alguien lo confirme o lo descarte con pruebas.',
            ],
            'agresivo' => [
                'frase' => 'Estás enfadado/a y a la defensiva desde el principio. Puede ser por la espera, por malas experiencias previas con médicos, o por tu situación personal. Respondes de forma cortante y con tono seco. Si sientes que el médico te juzga o no te toma en serio, subes el tono. Usas frases como "eso ya se lo dije al otro médico y no me hizo ni caso" o "¿para eso he esperado dos horas?". Si el médico mantiene la calma y te trata con respeto, puedes ir bajando la intensidad poco a poco, pero cualquier comentario desafortunado te vuelve a disparar.',
                'contexto' => 'Vienes cargado/a de frustración y cualquier señal de que no te toman en serio te dispara.',
            ],
            'deprimido' => [
                'frase' => 'Estás apático/a y sin energía. Hablas en voz baja, despacio, con pausas largas. Te cuesta encontrar las palabras y a veces no terminas las frases. Si el médico te pregunta cómo estás, respondes "ahí voy" o "tirando". No muestras mucho interés en el resultado de la consulta: "lo que usted vea" o "da igual". [miras al suelo] con frecuencia. Si el médico muestra empatía genuina, puedes emocionarte brevemente antes de volver a cerrarte. No tienes energía para elaborar respuestas largas.',
                'contexto' => 'Estás emocionalmente agotado/a y sientes que nada de lo que hagan va a cambiar realmente cómo te encuentras.',
            ],
            'desconfiado' => [
                'frase' => 'No confías en los médicos ni en el sistema sanitario. Puede ser por malas experiencias previas o por tu forma de ser. Cuestionas todo: "¿eso para qué es?", "¿es necesario de verdad?", "a mi vecino le dijeron lo mismo y luego resultó que era otra cosa". Si el médico te receta algo, preguntas por efectos secundarios. Si te propone pruebas, preguntas si son necesarias o si es por protocolo. No das información fácilmente porque sientes que puede usarse en tu contra. Si el médico se gana tu confianza con honestidad y transparencia, te abres más.',
                'contexto' => 'No te fías de que lo que te digan sea lo mejor para ti y necesitas verificar todo antes de aceptarlo.',
            ],
            'confuso' => [
                'frase' => 'Estás desorientado/a y te cuesta seguir la conversación. Te contradices sobre fechas y detalles sin darte cuenta: "empezó el martes... o era el miércoles, no sé". Mezclas síntomas actuales con episodios pasados. Si el médico te hace varias preguntas seguidas, te pierdes y respondes solo a la última. A veces repites cosas que ya has dicho como si no las hubieras dicho. Puedes irte por las ramas contando algo que no tiene relación con la pregunta.',
                'contexto' => 'Tu cabeza está saturada y te cuesta organizar lo que quieres decir, las ideas se te mezclan.',
            ],
            'evasivo' => [
                'frase' => 'Hablas con normalidad e incluso con soltura sobre temas que no te incomodan, pero cuando la conversación se acerca a algo que te toca, esquivas. Cambias de tema sutilmente, das respuestas vagas como "bueno, lo normal" o "como todo el mundo", o redirigir la atención a otro síntoma. No es que mientas: simplemente no quieres hablar de ciertos temas. Si el médico insiste con tacto, puedes acabar respondiendo con evasivas parciales. Si insiste de forma directa o brusca, te cierras completamente o respondes con un cortante "prefiero no hablar de eso".',
                'contexto' => 'Hay aspectos de tu situación que te resultan incómodos o vergonzosos y prefieres evitarlos si es posible.',
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
            1 => 'Muy escueto/a. Nunca más de una frase corta o una sola palabra. No elaboras nada. Si puedes responder con un gesto, lo haces: [asiente], [niega con la cabeza], [señala el pecho]. Ejemplos: "Aquí." "Desde ayer." "No sé." "Sí." Si el médico espera en silencio a que digas más, no añades nada.',
            2 => 'Escueto/a. Nunca más de una frase por respuesta. Respuestas cortas y directas. No das contexto ni detalles que no te hayan pedido. Ejemplos: "Me duele la espalda desde el lunes." "No, eso no." "Una pastilla blanca, por las mañanas." Si el médico necesita más información, tiene que preguntar específicamente.',
            3 => 'Normal. Respondes en una a tres frases. Das el dato principal y algún detalle relevante si te sale natural, pero no te extiendes. Ejemplos: "Me duele la espalda desde el lunes, sobre todo cuando me agacho." "Sí, me tomo una pastilla para la tensión, creo que es de las blancas pequeñas."',
            4 => 'Detallista. Respondes en dos a cinco frases. Das bastante contexto sin que te lo pidan. Tiendes a añadir circunstancias, opiniones y pequeñas anécdotas a tus respuestas. Ejemplos: "Pues mire, el lunes estaba en casa recogiendo la compra y al agacharme noté un dolor fuerte en la espalda baja, como un tirón. Desde entonces no puedo ni atarme los zapatos."',
            5 => 'Muy detallista. Respondes en tres a seis frases, pudiendo extenderte más cuando divagues. Tiendes a irte por las ramas y cuesta mantenerte centrado/a en la pregunta. Mezclas la información médica con anécdotas, opiniones y detalles irrelevantes. El médico tendrá que reconducirte a menudo. Ejemplos: "Ay, pues verá, el lunes estaba yo en el Mercadona, que ahora está todo por las nubes, y resulta que compré muchas cosas porque venía mi hija a comer, que vive en Alicante, ¿sabe?, y claro, al agacharme a coger un pack de agua noté ahí como un latigazo..." Las respuestas siempre incluyen contexto que no se ha pedido.',
        ];
        return $labels[$level] ?? $labels[3];
    }

    private function formatMedicalKnowledge(int $level): string
    {
        $labels = [
            1 => 'Ningún conocimiento médico. No entiendes términos técnicos: si el médico dice "taquicardia", preguntas "¿eso qué es?". Describes todo con palabras cotidianas: "me duele aquí" [señala], "tengo la tripa revuelta", "se me duerme el brazo", "noto como un peso aquí en el pecho". No sabes el nombre de tus medicaciones, las describes por color o forma: "la pastilla blanca pequeña que me tomo por la mañana".',
            2 => 'Conocimiento mínimo. Sabes lo muy básico por lo que te han dicho otros médicos: "creo que es la tensión", "me dijeron algo de azúcar en la sangre", "tengo el colesterol". No siempre usas los términos correctamente. No sabes distinguir entre tipos de medicamentos. Si el médico te explica algo técnico, necesitas que te lo traduzca.',
            3 => 'Conocimiento básico. Entiendes los términos más comunes porque llevas tiempo con alguna condición crónica o porque te lo han explicado: "tengo hipertensión", "me recetaron antiinflamatorios", "soy alérgico a la penicilina". Puedes seguir una explicación médica sencilla sin perderte, pero si se pone técnica te pierdes.',
            4 => 'Conocimiento moderado. Has leído sobre tu condición en internet o tienes alguien cercano en el ámbito sanitario. Usas algunos términos con soltura: "creo que podría ser una ciática", "tenía el colesterol LDL alto", "leí que podía ser por el reflujo gastroesofágico". A veces usas términos que has leído pero no entiendes del todo. Puedes autodiagnosticarte equivocadamente y defender tu teoría.',
            5 => 'Eres profesional sanitario (enfermero/a, fisioterapeuta, farmacéutico/a, u otro profesional de salud) — tu ocupación debe reflejar esto. Usas terminología técnica con naturalidad: "dolor precordial opresivo con irradiación a MSI", "llevo una semana con disnea de medianos esfuerzos". Puedes anticipar lo que el médico va a preguntar o sugerir pruebas. Esto puede hacer que seas más difícil de entrevistar porque diriges la conversación hacia tu propio diagnóstico.',
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
