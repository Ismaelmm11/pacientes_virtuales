<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Modelo para las preguntas del test de evaluación de un paciente.
 *
 * Cada paciente puede tener múltiples preguntas que el estudiante
 * debe responder tras la simulación. Los tipos soportados son:
 * - MULTIPLE_CHOICE: Opciones A/B/C/D con una respuesta correcta
 * - TRUE_FALSE: Verdadero o Falso con respuesta correcta
 * - OPEN_ENDED: Pregunta abierta sin respuesta correcta ni feedback
 *
 * RELACIONES:
 * - patient(): Pertenece a un paciente
 *
 * TABLA: questions
 */
class Question extends Model
{
    /**
     * La tabla no usa updated_at, solo created_at
     */
    const UPDATED_AT = null;

    protected $fillable = [
        'patient_id',
        'question_text',
        'question_type',
        'options',
        'correct_answer',
        'points',
        'feedback_correct',
        'feedback_incorrect',
    ];

    protected $casts = [
        'options' => 'array',
        'points'  => 'decimal:2',
    ];

    /* ===========================
     * CONSTANTES DE TIPO
     * =========================== */

    const TYPE_MULTIPLE_CHOICE = 'MULTIPLE_CHOICE';
    const TYPE_TRUE_FALSE      = 'TRUE_FALSE';
    const TYPE_OPEN_ENDED      = 'OPEN_ENDED';

    /**
     * Tipos disponibles con sus etiquetas en español
     */
    public static function typeLabels(): array
    {
        return [
            self::TYPE_MULTIPLE_CHOICE => 'Opción Múltiple',
            self::TYPE_TRUE_FALSE      => 'Verdadero / Falso',
            self::TYPE_OPEN_ENDED      => 'Pregunta Abierta',
        ];
    }

    /* ===========================
     * RELACIONES
     * =========================== */

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    /* ===========================
     * HELPERS
     * =========================== */

    /**
     * Indica si este tipo de pregunta tiene respuesta correcta
     */
    public function hasCorrectAnswer(): bool
    {
        return $this->question_type !== self::TYPE_OPEN_ENDED;
    }

    /**
     * Devuelve la etiqueta legible del tipo
     */
    public function getTypeLabelAttribute(): string
    {
        return self::typeLabels()[$this->question_type] ?? $this->question_type;
    }
}