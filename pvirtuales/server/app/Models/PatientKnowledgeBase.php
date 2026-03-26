<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Base de conocimiento del caso: la "verdad" médica y narrativa.
 * 
 * Contiene la historia que el paciente cuenta (subjetiva),
 * los datos médicos reales (objetivos) y toda la información
 * que la IA necesita para responder coherentemente.
 *
 * @property int $id
 * @property int $patient_id
 * @property string $frase_inicial Primera frase exacta al iniciar la consulta. Incluye el motivo de consulta.
 * @property string $historia_narrativa Relato del paciente sobre su situación.
 * @property string $diagnostico_real Diagnóstico que el paciente NO sabe. Contexto para la IA.
 * @property string|null $hallazgos_clave Datos cruciales que el estudiante debería identificar.
 * @property \ArrayObject $antecedentes_medicos Enfermedades previas, cirugías, alergias (JSON).
 * @property \ArrayObject $medicacion_tomada Fármacos actuales con adherencia (JSON).
 * @property \ArrayObject $sintomas_asociados Síntomas con reglas de revelación (JSON).
 * @property \ArrayObject $historia_familiar Antecedentes familiares (JSON).
 * @property \ArrayObject $entorno_familiar Contexto familiar y social (JSON).
 * @property \ArrayObject $hobbies Actividades y pasatiempos (JSON).
 * @property \ArrayObject $vicios Hábitos tóxicos con frecuencia y revelación (JSON).
 * @property string|null $motivo_consulta Motivo oficial de la visita médica.
 */
class PatientKnowledgeBase extends Model
{

    const UPDATED_AT = null;

    protected $table = 'patient_knowledge_base';

    protected $fillable = [
        'patient_id',
        'frase_inicial',
        'historia_narrativa',
        'diagnostico_real',
        'hallazgos_clave',
        'antecedentes_medicos',
        'medicacion_tomada',
        'sintomas_asociados',
        'historia_familiar',
        'entorno_familiar',
        'hobbies',
        'vicios',
        'motivo_consulta'
    ];

    protected $casts = [
        'antecedentes_medicos' => 'array',
        'medicacion_tomada'    => 'array',
        'sintomas_asociados'   => 'array',
        'historia_familiar'    => 'array',
        'entorno_familiar'     => 'array',
        'hobbies'              => 'array',
        'vicios'               => 'array',
    ];

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }
}