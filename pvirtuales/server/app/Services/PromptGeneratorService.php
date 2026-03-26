<?php

namespace App\Services;

use App\Models\Patient;

/**
 * Genera el prompt Markdown para la IA a partir de las relaciones del paciente.
 *
 * PRINCIPIO DE DISEÑO:
 * Este servicio NO distingue entre básico y avanzado para leer datos.
 * El PatientService ya se encargó de rellenar las tablas con los valores correctos.
 * Aquí la regla es simple: si un campo tiene datos → se incluye, si es null/vacío → se omite.
 *
 * ESTRUCTURA DEL PROMPT GENERADO:
 *
 *   ═══════════════════════════════════════════
 *   SECCIÓN 1 — INSTRUCCIONES MAESTRAS
 *   ═══════════════════════════════════════════
 *   Misión + Regla de Oro + Reglas de Creatividad (con ejemplo dinámico
 *   del paciente y frases de límite reales) + Nota pedagógica
 *
 *   ═══════════════════════════════════════════
 *   SECCIÓN 2 — PERFIL DEL PERSONAJE
 *   ═══════════════════════════════════════════
 *   Encabezado: "PERFIL: Roberto, camionero estresado"
 *   2.1 Rol e Identidad
 *   2.2 Psicología y Comportamiento (con conocimiento médico aquí)
 *
 *   ═══════════════════════════════════════════
 *   SECCIÓN 3 — BASE DE CONOCIMIENTO
 *   ═══════════════════════════════════════════
 *   Frase inicial + Motivo de consulta (como respuesta situacional)
 *   Síntomas organizados por tipo de revelación:
 *     → Espontáneos | Con pregunta | Ocultos | Mentiras | Exagerados
 *   Antecedentes + Medicación + Vicios + Historia familiar + Entorno
 *   Diagnóstico real (bloque sellado: NUNCA revelar)
 *
 *   ═══════════════════════════════════════════
 *   SECCIÓN 4 — LÓGICA DE CONVERSACIÓN
 *   ═══════════════════════════════════════════
 *   Reglas de interacción + Gatillos + Contradicciones + Cierre
 *   Instrucciones especiales del profesor
 *
 * NOTA: La sección de "Inicio de Simulación" ha sido eliminada.
 * El SimulationController inyecta la frase_inicial directamente
 * como primer mensaje del asistente. No hay que repetirla aquí.
 */
class PromptGeneratorService
{
    /**
     * Punto de entrada. Genera el prompt completo para un paciente.
     *
     * @param  Patient $patient  Con relaciones cargadas: identity, psychology, knowledgeBase, conversationLogic
     * @return string            Prompt Markdown listo para enviar a la IA
     */
    public function generate(Patient $patient): string
    {
        $identity = $patient->identity;
        $psychology = $patient->psychology;
        $knowledge = $patient->knowledgeBase;
        $logic = $patient->conversationLogic;

        $sections = [];

        $sections[] = $this->buildMasterInstructions($patient, $logic);
        $sections[] = $this->buildProfile($patient, $identity, $psychology);
        $sections[] = $this->buildKnowledgeBase($knowledge);
        $sections[] = $this->buildConversationLogic($logic, $psychology);

        return implode("\n\n", array_filter($sections, fn($s) => !empty(trim($s))));
    }

    /* =================================================================
     * SECCIÓN 1 — INSTRUCCIONES MAESTRAS
     *
     * Objetivo: decirle a la IA QUÉ ES y QUÉ REGLAS sigue.
     * Estructura:
     *   - Misión
     *   - Regla de Oro (nunca romper personaje)
     *   - Reglas de Creatividad:
     *       · Permisión de improvisar (con condición)
     *       · Ejemplo de coherencia ESPECÍFICO del paciente
     *       · Frases de límite REALES del paciente
     *       · Gestos no verbales
     *   - Regla de datos médicos (inventar o no)
     *   - Nota pedagógica (si hay objetivos)
     * ================================================================= */

    private function buildMasterInstructions(Patient $patient, $logic): string
    {
        $lines = [];

        $lines[] = "### INSTRUCCIONES MAESTRAS ###";
        $lines[] = "";

        // --- Misión ---
        $lines[] = "Tu misión es encarnar a un personaje para una simulación de entrevista clínica con fines educativos. Un estudiante de medicina o enfermería va a hablar contigo creyendo que eres un paciente real. Tu trabajo es hacer que esa experiencia sea lo más realista y educativa posible.";
        $lines[] = "";

        // --- Regla de Oro ---
        $lines[] = "**REGLA DE ORO:** Eres un PACIENTE, no un asistente de IA. NUNCA rompas el personaje. NUNCA des diagnósticos ni sugieras tratamientos. NUNCA uses lenguaje de asistente virtual. Si alguien te pregunta si eres una IA, responde como lo haría el personaje: con extrañeza o ignorando la pregunta.";
        $lines[] = "";

        // --- Reglas de Creatividad ---
        $lines[] = "**REGLAS DE CREATIVIDAD:**";
        $lines[] = "";
        $lines[] = "- **Puedes improvisar** conversación cotidiana (saludos, quejas sobre la espera, comentarios sobre el tiempo) siempre que sea coherente con tu personaje y su estado emocional.";
        $lines[] = "- **Puedes inventar detalles menores** (nombres de familiares, anécdotas, expresiones coloquiales) que enriquezcan la conversación, siempre que no contradigan los datos de tu ficha.";
        $lines[] = "";

        // Ejemplo de coherencia: dinámico si existe, genérico si no
        $primerEjemplo = $patient->coherenceExamples->first();
        $ejemploCoherencia = $primerEjemplo ? [
            'pregunta' => $primerEjemplo->question,
            'coherente' => $primerEjemplo->correct_answer,
            'incoherente' => $primerEjemplo->incorrect_answer,
        ] : null;
        if (is_array($ejemploCoherencia) && !empty($ejemploCoherencia['pregunta'])) {
            $ec = $ejemploCoherencia;
            $lines[] = "- **Ejemplo de cómo improvisar correctamente:**";
            $lines[] = "  Si el médico pregunta *\"{$ec['pregunta']}\"*:";
            $lines[] = "  - ✅ Respuesta coherente con tu personaje: *\"{$ec['coherente']}\"*";
            $lines[] = "  - ❌ Respuesta incoherente que NUNCA debes dar: *\"{$ec['incoherente']}\"*";
        } else {
            $lines[] = "- **Ejemplo de cómo improvisar correctamente:** Si el médico pregunta algo que no está en tu ficha, responde de forma coherente con tu estado físico y emocional actual. No respondas como si estuvieras sano/a o tranquilo/a si tu personaje no lo está.";
        }
        $lines[] = "";

        // Frases de límite: reales si existen, genéricas si no
        $frasesLimite = $logic->frases_limite;
        if (is_array($frasesLimite) && !empty($frasesLimite)) {
            $lines[] = "- **Cuando el médico pregunta por datos médicos que no tienes** (análisis, constantes, resultados de pruebas), usa frases como:";
            foreach ($frasesLimite as $frase) {
                if (!empty(trim($frase))) {
                    $lines[] = "  - *\"{$frase}\"*";
                }
            }
        } else {
            $lines[] = "- **Cuando el médico pregunta por datos médicos que no tienes** (análisis, constantes, resultados), responde como un paciente real: *\"No sé\"*, *\"No me han dicho eso\"*, *\"Eso lo tenéis vosotros en el ordenador\"*.";
        }
        $lines[] = "";

        // Gestos no verbales (siempre)
        $lines[] = "- **Comunicación no verbal:** Cuando sea natural, describe acciones físicas entre corchetes: [suspira], [se toca el pecho], [mira al suelo], [cruza los brazos]. No abuses. Úsalo solo cuando aporte información emocional relevante.";
        $lines[] = "";

        // Idioma
        $lines[] = "- **Idioma:** Habla siempre en español. Usa el registro que corresponde a tu nivel educativo y tu estado emocional. No uses términos médicos que tu personaje no conocería.";
        $lines[] = "";

        // --- Regla de datos médicos ---
        if ($patient->puede_inventar_datos_medicos) {
            $lines[] = "**DATOS MÉDICOS:** Si el médico pregunta por información médica no definida en tu ficha (constantes vitales, resultados de análisis, exploraciones), puedes improvisar una respuesta coherente con tu perfil y diagnóstico real. Siempre desde la perspectiva del paciente, no del médico.";
        } else {
            $lines[] = "**LÍMITE ABSOLUTO DE DATOS MÉDICOS:** Si el médico pregunta por información médica que no está en tu ficha (constantes vitales, analíticas, resultados de pruebas), NO inventes cifras ni datos clínicos. Responde como un paciente real que no sabe: *\"No me han mirado eso\"*, *\"No sé qué es eso\"*, *\"No me acuerdo del número\"*.";
        }
        $lines[] = "";

        // --- Nota pedagógica ---
        if (!empty($patient->learning_objectives)) {
            $lines[] = "**NOTA PEDAGÓGICA (no compartas esto con el médico):** Los objetivos educativos de este caso son: {$patient->learning_objectives}. Guía sutilmente la interacción para que estas áreas sean relevantes, pero NUNCA lo hagas de forma artificial ni rompas el personaje para conseguirlo.";
            $lines[] = "";
        }

        return implode("\n", $lines);
    }

    /* =================================================================
     * SECCIÓN 2 — PERFIL DEL PERSONAJE
     *
     * Objetivo: decirle a la IA QUIÉN ES.
     * Estructura:
     *   - Encabezado con nombre + descripción del caso
     *   2.1 Rol e Identidad
     *       · Rol principal (quién habla, con qué relación si hay acompañante)
     *       · Datos demográficos
     *       · Contexto sociolaboral
     *   2.2 Psicología y Comportamiento
     *       · Estado emocional dominante
     *       · Por qué se siente así (contexto)
     *       · Conflicto interno (solo si existe — avanzado)
     *       · Estilo de comunicación (verbosidad)
     *       · Nivel de conocimiento médico (aquí, no en identidad)
     *       · Preocupaciones (si existen)
     * ================================================================= */

    private function buildProfile(Patient $patient, $identity, $psychology): string
    {
        $lines = [];

        // --- Encabezado del personaje ---
        $titulo = strtoupper($patient->case_title);
        if (!empty($patient->patient_description)) {
            $titulo .= ", " . $patient->patient_description;
        }
        $lines[] = "---";
        $lines[] = "";
        $lines[] = "### PERFIL DEL PERSONAJE: {$titulo} ###";
        $lines[] = "";

        // === 2.1 ROL E IDENTIDAD ===
        $lines[] = "#### 2.1 Rol e Identidad";
        $lines[] = "";
        $lines[] = "**Quién eres:** {$identity->rol_principal}";
        $lines[] = "";
        $lines[] = "**Datos demográficos:** {$identity->datos_demograficos}";
        $lines[] = "";

        if (!empty($identity->contexto_sociolaboral) && $identity->contexto_sociolaboral !== 'Sin contexto adicional.') {
            $lines[] = "**Contexto personal y laboral:** {$identity->contexto_sociolaboral}";
            $lines[] = "";
        }

        // === 2.2 PSICOLOGÍA Y COMPORTAMIENTO ===
        $lines[] = "#### 2.2 Psicología y Comportamiento";
        $lines[] = "";

        // Estado emocional dominante
        $lines[] = "**Estado emocional dominante:** {$psychology->estado_emocional_frase}";
        $lines[] = "";

        // Por qué se siente así
        if (!empty($psychology->estado_emocional_contexto)) {
            $lines[] = "**Por qué te sientes así:** {$psychology->estado_emocional_contexto}";
            $lines[] = "";
        }

        // Conflicto interno (solo avanzado, si existe)
        if (!empty($psychology->conflicto_interno)) {
            $lines[] = "**Conflicto interno (lo que no dices pero sientes):** {$psychology->conflicto_interno}";
            $lines[] = "";
        }

        // Estilo de comunicación
        $comunicacion = (array) $psychology->caracteristicas_comunicacion;
        if (!empty($comunicacion['descripcion_verbosidad'])) {
            $lines[] = "**Estilo de comunicación:** {$comunicacion['descripcion_verbosidad']}";
            $lines[] = "";
        }

        // Conocimiento médico (aquí porque afecta al vocabulario del personaje)
        if (!empty($comunicacion['descripcion_conocimiento'])) {
            $lines[] = "**Nivel de conocimiento médico:** {$comunicacion['descripcion_conocimiento']}";
            $lines[] = "";
        }

        // Preocupaciones
        if (!empty($psychology->preocupaciones_ocultas)) {
            $lines[] = "**Preocupaciones (NO las menciones espontáneamente):** Tienes preocupaciones que NO revelarás a menos que el médico genere un ambiente de confianza o pregunte directamente. Entonces puedes revelarlas gradualmente:";
            $lines[] = $psychology->preocupaciones_ocultas;
            $lines[] = "";
        }

        return implode("\n", $lines);
    }

    /* =================================================================
     * SECCIÓN 3 — BASE DE CONOCIMIENTO
     *
     * Objetivo: decirle a la IA QUÉ SABE y CÓMO LO REVELA.
     * Estructura:
     *   - Frase inicial (entre comillas, reproducir textualmente)
     *   - Motivo de consulta (como respuesta situacional al "¿qué le pasa?")
     *   - Síntomas organizados por tipo de revelación:
     *       · Bloque 1: Espontáneos (los cuenta sin que pregunten)
     *       · Bloque 2: Con pregunta (solo si preguntan)
     *       · Bloque 3: Exagerados (los exagera)
     *       · Bloque 4: Ocultos (nunca los admite)
     *       · Bloque 5: Mentiras (dice otra cosa)
     *   - Antecedentes médicos
     *   - Medicación actual
     *   - Vicios / Hábitos tóxicos
     *   - Historia familiar
     *   - Entorno personal y social
     *   - Diagnóstico real (bloque sellado)
     * ================================================================= */

    private function buildKnowledgeBase($knowledge): string
    {
        $lines = [];

        $lines[] = "---";
        $lines[] = "";
        $lines[] = "### BASE DE CONOCIMIENTO ###";
        $lines[] = "";

        // --- Frase inicial ---
        $lines[] = "**Tu frase de apertura (ya ha sido dicha automáticamente al inicio de la consulta — NO la repitas):**";
        $lines[] = "";
        $lines[] = "> {$knowledge->frase_inicial}";
        $lines[] = "";
        $lines[] = "Esta fue tu primera intervención. Tenla en cuenta como contexto para mantener coherencia, pero no la vuelvas a decir.";

        // --- Motivo de consulta ---
        // Se presenta como respuesta situacional, no como dato clínico
        if (!empty($knowledge->motivo_consulta)) {
            $lines[] = "\nEsta es la **información completa**s del caso para tu coherencia interna. Cuando el médico pregunte, responde de forma natural y solo con los síntomas marcados como espontáneos";
            $lines[] = "";
            $lines[] = "> {$knowledge->motivo_consulta}";
            $lines[] = "";
        }

        // --- Síntomas organizados por tipo de revelación ---
        $sintomas = $knowledge->sintomas_asociados;
        if (is_array($sintomas) && !empty($sintomas)) {
            $this->buildSymptomsSection($sintomas, $lines);
        }

        // --- Antecedentes médicos ---
        $antecedentes = (array) $knowledge->antecedentes_medicos;
        if (!empty($antecedentes)) {

            // Básico: texto libre
            if (!empty($antecedentes['texto_libre'])) {
                $lines[] = "#### Antecedentes Médicos";
                $lines[] = "";
                $lines[] = "El paciente tiene los siguientes antecedentes. Solo los mencionará si el médico pregunta por su historial o algo relacionado:";
                $lines[] = "";
                $lines[] = $antecedentes['texto_libre'];
                $lines[] = "";
            }

            // Avanzado: enfermedades estructuradas
            if (!empty($antecedentes['enfermedades']) && is_array($antecedentes['enfermedades'])) {
                $lines[] = "#### Enfermedades Previas";
                $lines[] = "";
                foreach ($antecedentes['enfermedades'] as $e) {
                    $e = (array) $e;
                    $since = !empty($e['since']) ? " (desde hace {$e['since']})" : '';
                    $lines[] = "- **{$e['nombre']}**{$since} → " . $this->formatRevealRule($e);
                }
                $lines[] = "";
            }

            if (!empty($antecedentes['cirugias']) && is_array($antecedentes['cirugias'])) {
                $lines[] = "#### Cirugías Previas";
                $lines[] = "";
                foreach ($antecedentes['cirugias'] as $c) {
                    $c = (array) $c;
                    $since = !empty($c['since']) ? " (hace {$c['since']})" : '';
                    $lines[] = "- **{$c['nombre']}**{$since} → " . $this->formatRevealRule($c);
                }
                $lines[] = "";
            }

            if (!empty($antecedentes['alergias'])) {
                $lines[] = "#### Alergias";
                $lines[] = "";
                $lines[] = $antecedentes['alergias'];
                $lines[] = "";
            }
        }

        // --- Medicación ---
        $medicacion = $knowledge->medicacion_tomada;
        if (is_array($medicacion) && !empty($medicacion)) {
            $med = (array) $medicacion;
            $lines[] = "#### Medicación Actual";
            $lines[] = "";
            $lines[] = "Solo menciona la medicación si el médico pregunta directamente por ella:";
            $lines[] = "";

            // Básico: texto libre
            if (!empty($med['texto_libre'])) {
                $lines[] = $med['texto_libre'];
                $lines[] = "";
            }

            // Básico nuevo: lista simple con nombre + frecuencia
            if (isset($med[0])) {
                foreach ($medicacion as $m) {
                    $m = (array) $m;
                    $nombre = $m['nombre'] ?? '?';
                    $freq = !empty($m['frecuencia']) ? " — {$m['frecuencia']}" : '';
                    $dosis = !empty($m['dosis']) ? " {$m['dosis']}" : '';

                    $linea = "- **{$nombre}**{$dosis}{$freq}";

                    // Adherencia (avanzado)
                    if (isset($m['adherencia']) && !$m['adherencia']) {
                        $detalle = !empty($m['detalle_adherencia']) ? ": {$m['detalle_adherencia']}" : '';
                        $linea .= " ⚠️ No se lo toma correctamente{$detalle}";
                    }

                    // Revelación (si existe en avanzado)
                    if (!empty($m['revelacion'])) {
                        $linea .= " → " . $this->formatRevealRule($m);
                    }

                    $lines[] = $linea;
                }
                $lines[] = "";
            }
        }

        // --- Vicios ---
        $vicios = $knowledge->vicios;
        if (is_array($vicios) && !empty($vicios)) {
            $lines[] = "#### Hábitos Tóxicos";
            $lines[] = "";
            $lines[] = "Estos son hábitos reales del paciente. Presta atención a cómo los revela:";
            $lines[] = "";
            foreach ($vicios as $v) {
                $v = (array) $v;
                $nombre = $v['nombre'] ?? '?';
                $freq = !empty($v['frequency']) ? " ({$v['frequency']})" : '';
                $lines[] = "- **{$nombre}**{$freq} → " . $this->formatRevealRule($v);
            }
            $lines[] = "";
        }

        // --- Historia familiar ---
        $familiaHist = (array) $knowledge->historia_familiar;
        if (!empty($familiaHist['texto'])) {
            $lines[] = "#### Antecedentes Familiares";
            $lines[] = "";
            $lines[] = "Solo lo menciones si el médico pregunta por antecedentes familiares:";
            $lines[] = "";
            $lines[] = $familiaHist['texto'];
            $lines[] = "";
        }

        // --- Entorno personal ---
        $entorno = (array) $knowledge->entorno_familiar;
        $textoEntorno = $entorno['texto'] ?? ($entorno['contexto'] ?? null);
        if (!empty($textoEntorno)) {
            $lines[] = "#### Entorno Personal y Social";
            $lines[] = "";
            $lines[] = $textoEntorno;
            $lines[] = "";
        }

        // --- Diagnóstico real (bloque sellado) ---
        $lines[] = "---";
        $lines[] = "";
        $lines[] = "#### ⛔ VERDAD MÉDICA — NUNCA LA REVELES DIRECTAMENTE ⛔";
        $lines[] = "";
        $lines[] = "**Diagnóstico real:** {$knowledge->diagnostico_real}";
        $lines[] = "";
        $lines[] = "Tú NO sabes cuál es tu diagnóstico. Describes lo que sientes, no lo que tienes. Este dato es solo para que mantengas coherencia interna en tus respuestas.";
        $lines[] = "";

        if (!empty($knowledge->hallazgos_clave)) {
            $lines[] = "**Hallazgos clave que el estudiante debería identificar:** {$knowledge->hallazgos_clave}";
            $lines[] = "";
            $lines[] = "Estas pistas deben poder descubrirse mediante las preguntas adecuadas. NO las ofrezcas espontáneamente.";
            $lines[] = "";
        }

        return implode("\n", $lines);
    }

    /* =================================================================
     * HELPER: Síntomas organizados por tipo de revelación
     *
     * En vez de una lista plana, agrupa los síntomas en bloques
     * con instrucciones específicas para cada tipo. Esto es clave
     * para que la IA entienda bien cuándo y cómo revelar cada uno.
     * ================================================================= */

    private function buildSymptomsSection(array $sintomas, array &$lines): void
    {
        // Clasificar por tipo de revelación
        $espontaneos = [];
        $conPregunta = [];
        $exagerados = [];
        $ocultos = [];
        $mentiras = [];

        foreach ($sintomas as $s) {
            $s = (array) $s;
            if (empty($s['nombre']))
                continue;
            $tipo = $s['revelacion'] ?? 'espontaneo';

            match ($tipo) {
                'espontaneo' => $espontaneos[] = $s,
                'pregunta' => $conPregunta[] = $s,
                'exagera' => $exagerados[] = $s,
                'oculta' => $ocultos[] = $s,
                'miente' => $mentiras[] = $s,
                default => $espontaneos[] = $s,
            };
        }

        $lines[] = "#### Síntomas";
        $lines[] = "";

        // Bloque 1: Espontáneos
        if (!empty($espontaneos)) {
            $lines[] = "**Los mencionas espontáneamente** (los traes tú a la conversación sin que te pregunten):";
            $lines[] = "";
            foreach ($espontaneos as $s) {
                $lines[] = "- " . $this->formatSymptomLine($s);
            }
            $lines[] = "";
        }

        // Bloque 2: Con pregunta
        if (!empty($conPregunta)) {
            $lines[] = "**Solo los revelas si te preguntan** (el médico tiene que preguntar por el síntoma o algo relacionado para que lo menciones):";
            $lines[] = "";
            foreach ($conPregunta as $s) {
                $lines[] = "- " . $this->formatSymptomLine($s);
            }
            $lines[] = "";
        }

        // Bloque 3: Exagerados
        if (!empty($exagerados)) {
            $lines[] = "**Los exageras** (el síntoma existe pero lo describes de forma dramática o magnificada, más intensa de lo que es en realidad):";
            $lines[] = "";
            foreach ($exagerados as $s) {
                $lines[] = "- " . $this->formatSymptomLine($s);
            }
            $lines[] = "";
        }

        // Bloque 4: Ocultos
        if (!empty($ocultos)) {
            $lines[] = "**Los ocultas activamente** (NUNCA los admites aunque el médico pregunte directamente. Niégalos o esquiva la pregunta):";
            $lines[] = "";
            foreach ($ocultos as $s) {
                $lines[] = "- " . $this->formatSymptomLine($s);
            }
            $lines[] = "";
        }

        // Bloque 5: Mentiras
        if (!empty($mentiras)) {
            $lines[] = "**Mientes sobre estos** (cuando el médico pregunte, das la información falsa indicada y mantienes la mentira durante toda la consulta):";
            $lines[] = "";
            foreach ($mentiras as $s) {
                $s = (array) $s;
                $base = $this->formatSymptomLine($s);
                if (!empty($s['mentira'])) {
                    $lines[] = "- {$base} → En su lugar dices: *\"{$s['mentira']}\"*";
                } else {
                    $lines[] = "- {$base} → Inventa una mentira coherente con tu personaje y mantenla hasta el final.";
                }
            }
            $lines[] = "";
        }
    }

    /**
     * Formatea una línea de síntoma con sus detalles opcionales (avanzado).
     */
    private function formatSymptomLine(array $s): string
    {
        $nombre = $s['nombre'] ?? '?';
        $extras = [];

        if (!empty($s['intensidad']))
            $extras[] = "intensidad {$s['intensidad']}/10";
        if (!empty($s['agravantes']))
            $extras[] = "empeora con: {$s['agravantes']}";
        if (!empty($s['atenuantes']))
            $extras[] = "mejora con: {$s['atenuantes']}";

        return !empty($extras)
            ? "**{$nombre}** (" . implode(', ', $extras) . ")"
            : "**{$nombre}**";
    }

    /* =================================================================
     * SECCIÓN 4 — LÓGICA DE CONVERSACIÓN
     *
     * Objetivo: decirle a la IA CÓMO SE COMPORTA en situaciones concretas.
     * Estructura:
     *   - Reglas de interacción (Si... → Entonces...)
     *   - Gatillos emocionales (Si mencionan X → reacción)
     *   - Contradicciones intencionales (pistas pedagógicas)
     *   - Evento de cierre
     *   - Instrucciones especiales del profesor
     * ================================================================= */

    private function buildConversationLogic($logic, $psychology): string
    {
        $lines = [];
        $hasContent = false;

        $lines[] = "---";
        $lines[] = "";
        $lines[] = "### LÓGICA DE CONVERSACIÓN ###";
        $lines[] = "";

        // --- Reglas de interacción ---
        $reglas = $psychology->reglas_interaccion;
        if (is_array($reglas) && !empty($reglas)) {
            $hasContent = true;
            $lines[] = "#### Reglas de Comportamiento";
            $lines[] = "";
            foreach ($reglas as $r) {
                $r = (array) $r;
                $condicion = $r['condicion'] ?? '?';
                $reaccion = $r['reaccion'] ?? '?';
                $lines[] = "- **Si** el médico {$condicion} **→** {$reaccion}";
            }
            $lines[] = "";
        }

        // --- Gatillos emocionales ---
        $gatillos = $logic->gatillos_emocionales;
        if (is_array($gatillos) && !empty($gatillos)) {
            $hasContent = true;
            $lines[] = "#### Gatillos Emocionales";
            $lines[] = "";
            foreach ($gatillos as $g) {
                $g = (array) $g;
                $tema = $g['tema'] ?? '?';
                $reaccion = $g['reaccion'] ?? '?';
                $lines[] = "- **Si mencionan** *\"{$tema}\"* **→** {$reaccion}";
            }
            $lines[] = "";
        }

        // --- Contradicciones intencionales ---
        $contradicciones = $logic->contradicciones;
        if (is_array($contradicciones) && !empty($contradicciones)) {
            $hasContent = true;
            $lines[] = "#### Contradicciones Intencionales";
            $lines[] = "";
            $lines[] = "Debes cometer estas inconsistencias de forma natural durante la conversación. Son pistas pedagógicas que el médico debería detectar:";
            $lines[] = "";
            foreach ($contradicciones as $c) {
                $c = (array) $c;
                $dice = $c['que_dice'] ?? '?';
                $contradice = $c['que_contradice'] ?? '?';
                $caught = !empty($c['si_le_pillan'])
                    ? $c['si_le_pillan']
                    : 'Improvisa una excusa coherente con tu personaje.';
                $lines[] = "- **Dices:** *\"{$dice}\"* **→ Pero en realidad:** {$contradice}";
                $lines[] = "  - *Si te pillan en la contradicción:* {$caught}";
            }
            $lines[] = "";
        }

        // --- Evento de cierre ---
        $cierre = $logic->eventos_cierre;
        if (is_array($cierre) && !empty($cierre)) {
            $hasContent = true;
            $lines[] = "#### Cierre de la Consulta";
            $lines[] = "";

            if (isset($cierre['despedida_natural'])) {
                $lines[] = $cierre['despedida_natural'];
            } else {
                foreach ($cierre as $ev) {
                    $ev = (array) $ev;
                    $cond = $ev['condicion'] ?? '?';
                    $accion = $ev['accion'] ?? '?';
                    $lines[] = "- **{$cond}** → {$accion}";
                }
            }
            $lines[] = "";
        }

        // --- Instrucciones especiales del profesor ---
        if (!empty($logic->instrucciones_especiales)) {
            $hasContent = true;
            $lines[] = "#### Instrucciones Adicionales del Caso";
            $lines[] = "";
            $lines[] = $logic->instrucciones_especiales;
            $lines[] = "";
        }

        // Si no hay nada en esta sección, devolver vacío
        if (!$hasContent)
            return '';

        return implode("\n", $lines);
    }

    /* =================================================================
     * HELPERS: FORMATEO DE REGLAS DE REVELACIÓN
     * ================================================================= */

    /**
     * Convierte el campo 'revelacion' de un item en texto legible para el prompt.
     * Usado en antecedentes, medicación y vicios (los síntomas tienen su propio sistema).
     */
    private function formatRevealRule(array $item): string
    {
        $reveal = $item['revelacion'] ?? 'espontaneo';

        return match ($reveal) {
            'espontaneo' => 'Lo mencionas espontáneamente cuando sea natural.',
            'pregunta' => 'Solo lo mencionas si el médico pregunta directamente.',
            'exagera' => 'Lo mencionas exagerando su importancia o gravedad.',
            'oculta' => 'NUNCA lo admites aunque te pregunten. Niégalo o esquiva la pregunta.',
            'miente' => $this->formatLieRule($item),
            default => 'Lo mencionas cuando sea natural.',
        };
    }

    /**
     * Formatea la regla de mentira para antecedentes, medicación y vicios.
     */
    private function formatLieRule(array $item): string
    {
        $mentira = $item['mentira'] ?? null;

        return !empty($mentira)
            ? "MIENTES sobre esto. En su lugar dices: *\"{$mentira}\"*. Mantén esta mentira durante toda la consulta."
            : "MIENTES sobre esto. Inventa una mentira coherente con tu personaje y mantenla durante toda la consulta.";
    }
}