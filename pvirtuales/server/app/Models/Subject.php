<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Representa una Asignatura o Especialidad Médica.
 * * Sirve para categorizar los casos clínicos (ej: Cardiología, Pediatría).
 *
 * @property int $id
 * @property string $name Nombre de la asignatura (ej: "Urgencias Pediátricas").
 * @property int $created_by_user_id Id del usuario que ha creado la asignatura.
 * @property string $code Código que identifica a la asignatura
 * @property string $institution Institución a la que pertenece la asignatura.
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Patient[] $patients
 */
class Subject extends Model
{

    protected $table = 'subjects';

    /**
     * Deshabilitamos 'updated_at' porque no existe en la tabla
     */
    const UPDATED_AT = null;

    protected $fillable = [
        'name',
        'created_by_user_id',
        'code',
        'institution',
    ];


    /**
     * Obtiene todos los pacientes/casos asociados a esta asignatura.
     */
    public function patients()
    {
        return $this->hasMany(Patient::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }


    /* ===========================
     * MÉTODOS DE AYUDA
     * =========================== */
    /**
     * Alumnos inscritos en esta asignatura.
     * 
     * Recorre la tabla pivote 'subject_user' filtrando por role = 'student'
     * para devolver solo los usuarios que son alumnos de esta asignatura.
     * Uso: $subject->students
     */
    public function students()
    {
        return $this->belongsToMany(User::class, 'subject_user', 'subject_id', 'user_id')
            ->wherePivot('role', 'student');
    }

    /**
     * Profesores colaboradores de esta asignatura.
     * 
     * Recorre la tabla pivote 'subject_user' filtrando por role = 'collaborator'
     * para devolver solo los usuarios que son profesores colaboradores,
     * es decir, profesores invitados por el propietario de la asignatura.
     * Uso: $subject->collaborators
     */
    public function collaborators()
    {
        return $this->belongsToMany(User::class, 'subject_user', 'subject_id', 'user_id')
            ->wherePivot('role', 'collaborator');
    }
}