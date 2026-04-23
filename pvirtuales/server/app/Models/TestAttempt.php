<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Modelo principal que representa un intento de test realizado por un estudiante con un paciente virtual.
 * 
 * Cada vez que un estudiante completa un test de evaluación tras interactuar con un paciente virtual, se crea un registro de TestAttempt que almacena la información relevante del intento, incluyendo la transcripción de la entrevista simulada, la puntuación final obtenida y las relaciones con el usuario y el paciente involucrados.
 *
 * @property int $id
 * @property int $user_id ID del usuario que realizó el intento de test.
 * @property int $patient_id ID del paciente asociado al intento de test.
 * @property string|null $interview_transcript Transcripción completa de la entrevista simulada entre el estudiante y el paciente virtual.
 * @property double|null $final_score Puntuación final obtenida por el intento de test.
 * @property \Carbon\Carbon $created_at
 */
class TestAttempt extends Model
{
    /**
     * La tabla no usa updated_at, solo created_at
     */
    const UPDATED_AT = null;

    protected $fillable = [
        'user_id',
        'patient_id',
        'interview_transcript',
        'submitted_at',
        'final_score',
        'general_feedback',
    ];

    protected $casts = [
        'final_score' => 'decimal:2',
        'interview_transcript' => 'array',
        'submitted_at' => 'datetime',
    ];


    /* ===========================
     * RELACIONES
     * =========================== */

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    public function answers()
    {
        return $this->hasMany(Answer::class);
    }


}