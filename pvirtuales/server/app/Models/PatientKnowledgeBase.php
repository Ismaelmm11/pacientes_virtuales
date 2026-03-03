<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\AsArrayObject;

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
 */
class PatientKnowledgeBase extends Model
{
    use HasFactory;

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
    ];

    protected $casts = [
        'antecedentes_medicos' => AsArrayObject::class,
        'medicacion_tomada'    => AsArrayObject::class,
        'sintomas_asociados'   => AsArrayObject::class,
        'historia_familiar'    => AsArrayObject::class,
        'entorno_familiar'     => AsArrayObject::class,
        'hobbies'              => AsArrayObject::class,
        'vicios'               => AsArrayObject::class,
    ];

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }
}