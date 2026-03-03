<?php

namespace App\Services;

use App\Models\Patient;
use App\Models\PatientType;
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
 * Centraliza la creación completa de un paciente, rellenando TODAS las tablas
 * intermedias (identity, psychology, knowledge_base, conversation_logic)
 * y generando el prompt final en patient_prompts.
 *
 * Tanto el Modo Básico como el Avanzado pasan por aquí. La diferencia es que
 * el Básico envía menos campos y este servicio rellena el resto con defaults
 * inteligentes.
 *
 * FLUJO:
 * Formulario → StorePatientRequest (validación) → PatientController
 * → PatientService::createPatient() → Rellena 5 tablas → PromptGeneratorService
 *
 * TABLAS QUE RELLENA:
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
     * MÉTODO PÚBLICO PRINCIPAL
     * ================================================================= */

    /**
     * Crea un paciente completo con todos sus datos asociados.
     *
     * Todo se ejecuta dentro de una transacción DB. Si cualquier paso
     * falla, se hace rollback completo.
     *
     * @param array $data  Datos validados del StorePatientRequest
     * @param int   $userId ID del usuario creador (profesor)
     * @return Patient El paciente con todas sus relaciones cargadas
     */
    public function createPatient(array $data, int $userId): Patient
    {
        return DB::transaction(function () use ($data, $userId) {

            $mode = $data['mode'] ?? 'basic';

            // En Modo Básico, chief_complaint actúa como frase inicial del paciente
            if ($mode === 'basic') {
                $data['frase_inicial'] = $data['frase_inicial']
                    ?? $data['chief_complaint']
                    ?? 'Hola doctor, vengo porque no me encuentro bien.';

                $data['attendee_type'] = $data['attendee_type'] ?? 'patient';
            }

            // 2. Registro principal en patients
            $patient = Patient::create([
                'case_title' => $data['case_title'],
                'patient_description' => $data['patient_description'] ?? null, // NUEVO
                'created_by_user_id' => $userId,
                'subject_id' => 1,
                'mode' => $mode,
                'learning_objectives' => $data['learning_objectives'] ?? null,
                'puede_inventar_datos_medicos' => $data['puede_inventar_datos_medicos'] ?? false,
                'initial_message' => $data['frase_inicial'],
            ]);

            // 3-6. Tablas intermedias
            $this->createRoleIdentity($patient, $data);
            $this->createPsychology($patient, $data, $mode);
            $this->createKnowledgeBase($patient, $data, $mode);
            $this->createConversationLogic($patient, $data, $mode);

            // 7. Generar prompt Markdown y guardarlo
            $this->generateAndStorePrompt($patient, $data);

            return $patient;
        });
    }

    /* =================================================================
     * TABLA: patient_role_identity
     * ================================================================= */

    /**
     * Crea la identidad y rol del paciente.
     *
     * Genera los textos descriptivos a partir de los campos del formulario.
     * Si es acompañante, el rol_principal refleja que habla un familiar
     * en nombre del paciente.
     */
    private function createRoleIdentity(Patient $patient, array $data): PatientRoleIdentity
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

        // --- Rol principal ---
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

        // --- Datos demográficos ---
        $datosDemograficos = "{$name}, {$age} años, {$genderText}.";
        if (!empty($data['education_level'])) {
            $datosDemograficos .= " Nivel educativo: " . $this->formatEducationLevel($data['education_level']) . ".";
        }

        // --- Contexto sociolaboral ---
        $contexto = '';
        if (!empty($data['occupation'])) {
            $contexto = $data['occupation'];
        }
        if (!empty($data['personal_context'])) {
            $contexto .= ($contexto ? '. ' : '') . $data['personal_context'];
        }

        // --- Nivel de conocimiento médico ---
        $nivelConocimiento = $this->formatMedicalKnowledge($data['medical_knowledge'] ?? 2);

        return PatientRoleIdentity::create([
            'patient_id' => $patient->id,
            // Campos de acompañante
            'es_acompanante' => $isCompanion,
            'nombre_acompanante' => $isCompanion ? ($data['companion_name'] ?? null) : null,
            'relacion_con_paciente' => $isCompanion ? ($data['companion_relation'] ?? null) : null,
            'edad_acompanante' => $isCompanion ? ($data['companion_age'] ?? null) : null,
            'genero_acompanante' => $isCompanion ? ($data['companion_gender'] ?? null) : null,
            // Campos de identidad
            'rol_principal' => $rolPrincipal,
            'datos_demograficos' => $datosDemograficos,
            'contexto_sociolaboral' => $contexto ?: 'Sin contexto adicional.',
            'nivel_conocimiento' => $nivelConocimiento,
            'campos_custom' => null,
        ]);
    }

    /* =================================================================
     * TABLA: patient_psychology
     * ================================================================= */

    /**
     * Crea el perfil psicológico y estilo de comunicación.
     *
     * Traduce el tipo de personalidad y los sliders a textos para la IA.
     * Si el usuario personalizó el texto (personality_custom), se usa ese.
     * En avanzado se permite emotional_context y reglas de interacción.
     */
    private function createPsychology(Patient $patient, array $data, string $mode): PatientPsychology
    {
        $personality = $data['personality_type'] ?? 'colaborador';
        $verbosity = (int) ($data['verbosity_level'] ?? 3);

        $emotionalDefaults = $this->getEmotionalDefaults($personality);

        // Frase emocional: custom del usuario o generada
        $estadoFrase = !empty($data['personality_custom'])
            ? $data['personality_custom']
            : $emotionalDefaults['frase'];

        // Contexto emocional: solo viene en avanzado
        $estadoContexto = $data['emotional_context'] ?? $emotionalDefaults['contexto'];

        // Conflicto interno: solo avanzado
        $conflictoInterno = ($mode === 'advanced') ? ($data['conflicto_interno'] ?? null) : null; // NUEVO

        // Características de comunicación con soporte de customización
        $comunicacion = $this->buildCommunicationTraits($personality, $verbosity, $data); // MODIFICADO

        // Reglas de interacción: solo avanzado
        $reglasInteraccion = null;
        if ($mode === 'advanced' && !empty($data['rules'])) {
            $reglasInteraccion = array_map(fn($rule) => [
                'condicion' => $rule['condition'] ?? '',
                'reaccion' => $rule['reaction'] ?? '',
            ], $data['rules']);
        }

        return PatientPsychology::create([
            'patient_id' => $patient->id,
            'estado_emocional_frase' => $estadoFrase,
            'estado_emocional_contexto' => $estadoContexto,
            'conflicto_interno' => $conflictoInterno, // NUEVO
            'caracteristicas_comunicacion' => $comunicacion,
            'reglas_interaccion' => $reglasInteraccion,
            'preocupaciones_ocultas' => $data['hidden_concerns'] ?? null,
        ]);
    }
    /* =================================================================
     * TABLA: patient_knowledge_base
     * ================================================================= */

    /**
     * Crea la base de conocimiento médico y narrativo del caso.
     *
     * En Modo Básico:
     *   - medical_history y current_medications son textareas simples
     *   - Se convierten a arrays JSON con formato unificado
     *   - La historia_narrativa se genera automáticamente
     *
     * En Modo Avanzado:
     *   - Listas estructuradas (diseases, surgeries, medications, vices)
     *   - Cada item con su regla de revelación
     */
    private function createKnowledgeBase(Patient $patient, array $data, string $mode): PatientKnowledgeBase
    {
        $isAdvanced = $mode === 'advanced';

        // --- Antecedentes médicos ---
        if ($isAdvanced) {
            $antecedentes = [
                'enfermedades' => $this->formatDynamicListWithReveal($data['diseases'] ?? []),
                'cirugias' => $this->formatDynamicListWithReveal($data['surgeries'] ?? []),
                'alergias' => $data['allergies'] ?? null,
            ];
        } else {
            $antecedentes = [
                'texto_libre' => $data['medical_history'] ?? null,
            ];
        }

        // --- Medicación ---
        if ($isAdvanced) {
            $medicacion = $this->formatMedications($data['medications'] ?? []);
        } else {
            // BÁSICO: lista dinámica simple con nombre + frecuencia
            $medicacion = $this->formatBasicMedications($data['medications'] ?? []); // NUEVO
        }

        // --- Síntomas ---
        $sintomas = $this->formatSymptoms($data['symptoms'] ?? [], $isAdvanced);

        // --- Vicios (AMBOS modos ahora) --- // MODIFICADO
        $vicios = [];
        if (!empty($data['vices'])) {
            $vicios = $this->formatDynamicListWithReveal($data['vices'] ?? [], 'frequency');
        }

        // --- Historia narrativa ---
        $historiaNarrativa = $data['historia_narrativa'] ?? $this->buildNarrative($data, $mode);

        // --- Historia familiar (AMBOS modos ahora) --- // MODIFICADO
        $historiaFamiliar = [];
        if (!empty($data['family_history'])) {
            $historiaFamiliar = ['texto' => $data['family_history']];
        }

        // --- Entorno familiar (AMBOS modos ahora) --- // MODIFICADO
        $entornoFamiliar = [];
        if (!empty($data['personal_context'])) {
            $entornoFamiliar = ['texto' => $data['personal_context']];
        }

        return PatientKnowledgeBase::create([
            'patient_id' => $patient->id,
            'frase_inicial' => $data['frase_inicial'],
            'motivo_consulta' => $data['motivo_consulta'] ?? null, // NUEVO
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
        ]);
    }

    /**
     * Formatea medicación del modo básico.
     * Solo recoge nombre y cuándo se toma (frecuencia libre).
     */
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

    /* =================================================================
     * TABLA: patient_conversation_logic
     * ================================================================= */

    /**
     * Crea la lógica de conversación (revelación, gatillos, contradicciones).
     *
     * En AMBOS modos se genera revelacion_sintomas desde los síntomas.
     * En Avanzado se añaden gatillos, contradicciones y eventos de cierre.
     */
    private function createConversationLogic(Patient $patient, array $data, string $mode): PatientConversationLogic
    {
        $isAdvanced = $mode === 'advanced';

        // --- Revelación de síntomas (ambos modos) ---
        $revelacionSintomas = $this->buildSymptomRevealRules($data['symptoms'] ?? []);

        // --- Gatillos emocionales (solo avanzado) ---
        $gatillos = null;
        if ($isAdvanced && !empty($data['triggers'])) {
            $gatillos = array_map(fn($t) => [
                'tema' => $t['topic'] ?? '',
                'reaccion' => $t['reaction'] ?? '',
            ], $data['triggers']);
        }

        // --- Contradicciones (solo avanzado) ---
        $contradicciones = null;
        if ($isAdvanced && !empty($data['contradictions'])) {
            $contradicciones = array_map(fn($c) => [
                'que_dice' => $c['says'] ?? '',
                'que_contradice' => $c['contradicts'] ?? '',
                'si_le_pillan' => $c['caught'] ?? '',
            ], $data['contradictions']);
        }

        // --- Eventos de cierre ---
        $eventosCierre = ['despedida_natural' => 'El paciente se despide cuando siente que la consulta ha terminado.'];
        if ($isAdvanced && !empty($data['closures'])) {
            $eventosCierre = array_map(fn($c) => [
                'condicion' => $c['condition'] ?? '',
                'accion' => $c['action'] ?? '',
            ], $data['closures']);
        }

        // --- Frases de límite (NUEVO - ambos modos) ---
        $frasesLimite = null;
        if (!empty($data['frases_limite'])) {
            $frasesLimite = array_values(
                array_filter($data['frases_limite'], fn($f) => !empty(trim($f)))
            );
        }

        // --- Ejemplo de coherencia (NUEVO - ambos modos) ---
        $ejemploCoherencia = null;
        $ec = $data['ejemplo_coherencia'] ?? [];
        if (!empty($ec['pregunta']) && !empty($ec['coherente']) && !empty($ec['incoherente'])) {
            $ejemploCoherencia = [
                'pregunta' => $ec['pregunta'],
                'coherente' => $ec['coherente'],
                'incoherente' => $ec['incoherente'],
            ];
        }

        return PatientConversationLogic::create([
            'patient_id' => $patient->id,
            'revelacion_sintomas' => $revelacionSintomas,
            'gatillos_emocionales' => $gatillos,
            'contradicciones' => $contradicciones,
            'interacciones_trigger' => null,
            'eventos_cierre' => $eventosCierre,
            'instrucciones_especiales' => $data['special_instructions'] ?? null,
            'frases_limite' => $frasesLimite,           // NUEVO
            'ejemplo_coherencia' => $ejemploCoherencia,      // NUEVO
        ]);
    }

    /* =================================================================
     * GENERACIÓN DEL PROMPT
     * ================================================================= */

    /**
     * Genera el prompt Markdown y lo guarda en patient_prompts.
     *
     * Recarga las relaciones del paciente para que el PromptGeneratorService
     * lea directamente de las tablas (no de $data).
     */
    private function generateAndStorePrompt(Patient $patient, array $data): PatientPrompt
    {
        $patient->load(['identity', 'psychology', 'knowledgeBase', 'conversationLogic']);

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
     * MÉTODOS AUXILIARES: FORMATEO DE ARRAYS DINÁMICOS
     * ================================================================= */

    /**
     * Formatea síntomas del formulario al formato JSON de BD.
     *
     * Básico: {name, reveal, lie_text}
     * Avanzado: + {intensity, aggravating, relieving}
     */
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

            // NUEVO: exagera no necesita campo extra,
            // la IA improvisa la exageración siendo coherente con el personaje
            // El valor 'exagera' queda registrado en revelacion y el prompt lo usará

            if ($isAdvanced) {
                $item['intensidad'] = $s['intensity'] ?? null;
                $item['agravantes'] = $s['aggravating'] ?? null;
                $item['atenuantes'] = $s['relieving'] ?? null;
            }

            $formatted[] = $item;
        }
        return $formatted;
    }

    /**
     * Formatea una lista dinámica genérica con revelación unificada.
     * Sirve para: diseases, surgeries, vices.
     *
     * @param array  $items      Los items del formulario
     * @param string $extraField Campo extra además de 'name' (ej: 'since', 'frequency')
     */
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

    /**
     * Formatea medicación estructurada (solo avanzado).
     * Incluye: nombre, dosis, frecuencia, adherencia, revelación.
     */
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
                'adherencia' => !empty($med['adherence']),
                'revelacion' => $med['reveal'] ?? 'espontaneo',
            ];

            if (empty($med['adherence']) && !empty($med['adherence_detail'])) {
                $entry['detalle_adherencia'] = $med['adherence_detail'];
            }

            if (($med['reveal'] ?? '') === 'miente') {
                $entry['mentira'] = $med['lie_text'] ?? null;
            }

            $formatted[] = $entry;
        }
        return $formatted;
    }

    /**
     * Construye las reglas de revelación de síntomas para conversation_logic.
     * Incluye el campo de mentira cuando reveal='miente'.
     */
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

    /**
     * Construye una narrativa básica combinando los datos del formulario.
     * Se usa cuando no se proporciona 'historia_narrativa' (modo básico).
     */
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

    /**
     * Devuelve estado emocional por defecto según el tipo de personalidad.
     * Cubre los 11 tipos del selector.
     */
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

    /**
     * Genera las características de comunicación como array para JSON.
     */
    private function buildCommunicationTraits(string $personality, int $verbosity, array $data = []): array
    {
        return [
            'personalidad' => $personality,
            'nivel_verbosidad' => $verbosity,
            'descripcion_verbosidad' => !empty($data['verbosity_custom'])  // NUEVO
                ? $data['verbosity_custom']
                : $this->formatVerbosity($verbosity),
            'nivel_conocimiento' => (int) ($data['medical_knowledge'] ?? 2),
            'descripcion_conocimiento' => !empty($data['knowledge_custom']) // NUEVO
                ? $data['knowledge_custom']
                : $this->formatMedicalKnowledge((int) ($data['medical_knowledge'] ?? 2)),
        ];
    }

    /* =================================================================
     * MÉTODOS AUXILIARES: FORMATEO DE VALORES
     * ================================================================= */

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

    /**
     * Formatea la relación del acompañante en texto legible.
     */
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