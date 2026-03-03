<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Modelo principal que representa un Paciente Virtual (Caso Clínico).
 * 
 * Actúa como entidad central que conecta la identidad, psicología, 
 * conocimientos y lógica conversacional del personaje.
 *
 * @property int $id
 * @property string $case_title Título descriptivo del caso.
 * @property int $created_by_user_id ID del profesor creador.
 * @property int $subject_id ID de la asignatura vinculada.
 * @property int $patient_type_id ID del tipo (especialidad médica).
 * @property string $mode Modo de creación: 'basic' o 'advanced'.
 * @property string|null $learning_objectives Objetivos de aprendizaje del caso.
 * @property bool $puede_inventar_datos_medicos Si es TRUE, la IA puede improvisar síntomas menores.
 * @property string|null $initial_message Frase inicial del paciente en la consulta.
 * @property bool $is_published Si es TRUE, el paciente está disponible para simulaciones.
 * @property \Carbon\Carbon $created_at
 */
class Patient extends Model
{
    use HasFactory;

    /**
     * Deshabilitamos 'updated_at' porque no existe en la tabla.
     * 'created_at' sí existe y lo gestiona la BD con DEFAULT current_timestamp().
     */
    const UPDATED_AT = null;

    protected $fillable = [
        'patient_type_id',
        'case_title',
        'created_by_user_id',
        'subject_id',
        'mode',
        'learning_objectives',
        'puede_inventar_datos_medicos',
        'initial_message',
        'is_published',
    ];

    protected $casts = [
        'puede_inventar_datos_medicos' => 'boolean',
        'is_published' => 'boolean',
        'created_at' => 'datetime',
    ];

    /* -----------------------------------------------------------------
     * RELACIONES - Todas las tablas del paciente
     * ----------------------------------------------------------------- */

    public function questions()
    {
        return $this->hasMany(\App\Models\Question::class);
    }

    public function hasTest(): bool
    {
        return $this->questions()->exists();
    }

    /** Asignatura a la que pertenece el caso. */
    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    /** Identidad básica y rol del paciente (Quién es). */
    public function identity()
    {
        return $this->hasOne(PatientRoleIdentity::class);
    }

    /** Perfil psicológico y estilo de comunicación (Cómo actúa). */
    public function psychology()
    {
        return $this->hasOne(PatientPsychology::class);
    }

    /** Base de conocimientos médica y narrativa (Qué sabe/recuerda). */
    public function knowledgeBase()
    {
        return $this->hasOne(PatientKnowledgeBase::class);
    }

    /** Lógica de triggers, revelación de síntomas y eventos de cierre. */
    public function conversationLogic()
    {
        return $this->hasOne(PatientConversationLogic::class);
    }

    /** Prompt compilado y versionado para la IA. */
    public function prompt()
    {
        return $this->hasOne(PatientPrompt::class);
    }
}