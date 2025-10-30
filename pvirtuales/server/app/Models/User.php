<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

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
        'role_id'
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
        'email_verified_at' => 'datetime',
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
            get: fn () => "{$this->first_name} {$this->last_name}"
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
}