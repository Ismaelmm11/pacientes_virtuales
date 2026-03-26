<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Representa una Asignatura o Especialidad Médica.
 * * Sirve para categorizar los casos clínicos (ej: Cardiología, Pediatría).
 *
 * @property int $id
 * @property int $patient_id Id que identifica al paciente.
 * @property string $question Pregunta de ejemplo que le realizan al paciente.
 * @property string $correct_answer Respuesta correcta y con sentido a la pregunta.
 * @property string $incorrect_answer Respuesta incorrecta y sin sentido a la pregunta.
 * @property int $example_order Orden de la pregunta dentro del caso clínico.
 * @property \Carbon\Carbon $created_at
 */
class CoherenceExample extends Model 
{

    /**
     * Deshabilitamos 'updated_at' porque no existe en la tabla
     */
    const UPDATED_AT = null;

    protected $fillable = [
        'patient_id',
        'question',
        'correct_answer',
        'incorrect_answer',
        'example_order',
        'institution',
    ];
    
    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }
}