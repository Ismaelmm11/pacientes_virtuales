<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Modelo que representa la relación entre un usuario y una asignatura, incluyendo el rol del usuario dentro de esa asignatura (colaborador o estudiante). Esta tabla pivote permite gestionar los permisos y accesos de los usuarios a los casos clínicos asociados a cada asignatura, así como facilitar la administración de los grupos de estudiantes y profesores dentro de la plataforma.
 * 
 * @property int $id
 * @property int $user_id ID del usuario.
 * @property int $subject_id Id de la asignatura.
 * @property string $role Rol del usuario
 */
class SubjectUser extends Model
{

    protected $table = 'subject_user';

    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'subject_id',
        'role',
    ];

    /* ===========================
     * RELACIONES
     * =========================== */

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    /* ===========================
     * MÉTODOS DE AYUDA
     * =========================== */

    /**
     * Verifica si el usuario es estudiante
     */
    public function isStudent(): bool
    {
        return $this->role === 'student';
    }

    /**
     * Verifica si el usuario es profesor
     */
    public function isCollaborator(): bool
    {
        return $this->role === 'collaborator';
    }
    
}