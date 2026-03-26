<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Modelo principal que representa una respuesta dada por un estudiante a una pregunta de un test.
 * 
 * Actúa como un registro de cada intento de respuesta.
 *
 * @property int $id
 * @property int $test_attempt_id ID que identifica al intento de test.
 * @property int $question_id ID de la pregunta que se responde.
 * @property string $given_answer Respuesta dada por el estudiante.
 * @property int|null $is_correct 1 si la respuesta es correcta, 0 si es incorrecta, NULL si no aplica (pregunta abierta).
 * @property double|null $score Puntuación obtenida por esta respuesta, calculada según la lógica de cada pregunta.
 * @property string|null $feedback Comentario de retroalimentación sobre la respuesta dada.
 * @property \Carbon\Carbon $created_at
 */
class Answer extends Model
{
    /**
     * La tabla no usa updated_at, solo created_at
     */
    const UPDATED_AT = null;

    protected $fillable = [
        'test_attempt_id',
        'question_id',
        'given_answer',
        'is_correct',
        'score',
        'feedback',
    ];

    protected $casts = [
        'score'  => 'decimal:2',
    ];

    /* ===========================
     * RELACIONES
     * =========================== */

    public function test()
    {
        return $this->belongsTo(TestAttempt::class);
    }

    public function question()
    {
        return $this->belongsTo(Question::class);
    }
}