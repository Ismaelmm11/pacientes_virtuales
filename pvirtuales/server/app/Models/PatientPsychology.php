<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Perfil psicológico y estilo de comunicación del paciente (o acompañante).
 * 
 * Define cómo actúa, cómo habla, qué siente y qué oculta.
 *
 * @property int $id
 * @property int $patient_id
 * @property string $estado_emocional_frase Emoción dominante en una frase.
 * @property string $estado_emocional_contexto Porqué de esa emoción (solo avanzado).
 * @property array $caracteristicas_comunicacion Rasgos de comunicación (JSON).
 * @property array|null $reglas_interaccion Reglas condicionales "Si X → Entonces Y" (JSON).
 * @property string|null $preocupaciones_ocultas Miedos que no expresa directamente.
 * @property string|null $conflicto_interno Tensión interna del paciente, por ejemplo querer negar que fuma.
 */
class PatientPsychology extends Model
{

    const UPDATED_AT = null;

    protected $table = 'patient_psychology';

    protected $fillable = [
        'patient_id',
        'estado_emocional_frase',
        'estado_emocional_contexto',
        'caracteristicas_comunicacion',
        'reglas_interaccion',
        'preocupaciones_ocultas',
        'conflicto_interno'
    ];

    protected $casts = [
        'caracteristicas_comunicacion' => 'array',
        'reglas_interaccion'           => 'array',
    ];

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }
}