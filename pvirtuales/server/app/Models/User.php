<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Modelo de usuario principal para la autenticación y perfiles.
 * * @property int $id
 * @property string $first_name Nombre del usuario.
 * @property string $last_name Apellidos del usuario.
 * @property string $email Correo electrónico único para login.
 * @property string $password Contraseña hasheada.
 * @property string $birth_date Fecha de nacimiento del usuario.
 * @property string $gender Género del usuario (opcional).
 * @property int $role_id Referencia al rol asignado.
 * @property string $auth_at Sirve para saber como se ha creado el usuario.
 * @property-read string $full_name Accessor para nombre y apellidos.
 */

class User extends Authenticatable
{
    use Notifiable;

    /**
     * Deshabilitamos 'updated_at' porque no existe en la tabla
     */
    const UPDATED_AT = null;

    /**
     * Los atributos que se pueden asignar masivamente
     */
    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'password',
        'birth_date',
        'gender',
        'role_id',
        'auth_at'
    ];

    /**
     * Los atributos que deben ocultarse en las serializaciones
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Los atributos que deben ser convertidos a tipos nativos
     */
    protected $casts = [
        'password' => 'hashed',
        'birth_date' => 'date',
    ];

    /**
     * ============================================
     * RELACIONES
     * ============================================
     */

    /**
     * Un usuario pertenece a un rol
     */
    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    /**
     * ============================================
     * ACCESSORS
     * ============================================
     */

    /**
     * Obtiene el nombre completo del usuario
     * Uso: $user->full_name
     */
    protected function fullName(): Attribute
    {
        return Attribute::make(
            get: fn() => "{$this->first_name} {$this->last_name}"
        );
    }

    /**
     * ============================================
     * MÉTODOS HELPER (ROLES)
     * ============================================
     */

    /**
     * Verifica si el usuario tiene un rol específico por ID
     */
    public function hasRole(int $roleId): bool
    {
        return $this->role_id === $roleId;
    }

    /**
     * Verifica si el usuario es administrador
     */
    public function isAdmin(): bool
    {
        return $this->hasRole(Role::ADMIN_ID);
    }

    /**
     * Verifica si el usuario es profesor
     */
    public function isTeacher(): bool
    {
        return $this->hasRole(Role::TEACHER_ID);
    }

    /**
     * Verifica si el usuario es estudiante
     */
    public function isStudent(): bool
    {
        return $this->hasRole(Role::STUDENT_ID);
    }

    /**
     * Asignaturas en las que el usuario está inscrito como alumno.
     * Uso: $user->enrolledSubjects
     */
    public function enrolledSubjects()
    {
        return $this->belongsToMany(Subject::class, 'subject_user', 'user_id', 'subject_id')
            ->wherePivot('role', 'student');
    }

    /**
     * Asignaturas en las que el usuario participa como profesor colaborador.
     * Uso: $user->collaboratingSubjects
     */
    public function collaboratingSubjects()
    {
        return $this->belongsToMany(Subject::class, 'subject_user', 'user_id', 'subject_id')
            ->wherePivot('role', 'collaborator');
    }

    /**
     * Asignaturas creadas por este usuario (si es profesor propietario).
     * Uso: $user->subjects
     */
    public function subjects()
    {
        return $this->hasMany(Subject::class, 'created_by_user_id');
    }

    /**
     * Pacientes creados por este usuario (si es profesor).
     * Uso: $user->patients
     */
    public function patients()
    {
        return $this->hasMany(Patient::class, 'created_by_user_id');
    }

    /**
     * Intentos de simulación realizados por este usuario (si es alumno).
     * Uso: $user->testAttempts
     */
    public function testAttempts()
    {
        return $this->hasMany(TestAttempt::class);
    }
}