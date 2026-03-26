<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Lógica de conversación: reglas de revelación, triggers, contradicciones y cierre.
 * 
 * Controla CÓMO el paciente interactúa durante la simulación:
 * qué revela, cuándo, qué le hace reaccionar y cómo termina la consulta.
 *
 * @property int $id
 * @property int $patient_id
 * @property array|null $revelacion_sintomas Reglas de cuándo revela cada síntoma (JSON).
 * @property array|null $gatillos_emocionales Reacciones ante temas o actitudes (JSON).
 * @property array|null $contradicciones Inconsistencias intencionales para que el estudiante detecte (JSON, solo avanzado).
 * @property array|null $interacciones_trigger Reglas generales "Si X → Entonces Y" (JSON).
 * @property array $eventos_cierre Condiciones de finalización de la consulta (JSON).
 * @property string|null $instrucciones_especiales Reglas adicionales en texto libre.
 * @property array|null $frases_limite Frases que el paciente dice cuando se le presiona demasiado o se hace una pregunta inapropiada (JSON).
 */
class PatientConversationLogic extends Model
{

    const UPDATED_AT = null;

    protected $table = 'patient_conversation_logic';

    protected $fillable = [
        'patient_id',
        'revelacion_sintomas',
        'gatillos_emocionales',
        'contradicciones',
        'interacciones_trigger',
        'eventos_cierre',
        'instrucciones_especiales',
        'frases_limite'
    ];

    protected $casts = [
        'revelacion_sintomas'   => 'array',
        'gatillos_emocionales'  => 'array',
        'contradicciones'       => 'array',
        'interacciones_trigger' => 'array',
        'eventos_cierre'        => 'array',
        'frases_limite'         => 'array',
    ];

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }
}