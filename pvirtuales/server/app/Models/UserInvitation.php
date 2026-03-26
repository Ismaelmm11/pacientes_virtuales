<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Modelo para gestionar los tokens de invitación de nuevos usuarios.
 * * @property int $id
 * @property string $email Correo electrónico al que se envía la invitación.
 * @property string $token Identificador único para el enlace de registro.
 * @property int $role_id ID del rol que se le asignará al usuario invitado.
 * @property int $invited_by_user_id ID del usuario que realiza la invitación.
 * @property int $subject_id ID de la asignatura a la que es invitado.
 * @property \Carbon\Carbon $created_at Fecha de creación de la invitación.
 */ 


class UserInvitation extends Model
{
    // Le decimos a Laravel que NO use la columna 'updated_at'
    const UPDATED_AT = null;

    /**
     * Los campos que permitimos rellenar.
     */
    protected $fillable = [
        'email',
        'token',
        'role_id',
        'invited_by_user_id',
        'subject_id',
    ];

    /* ===========================
     * RELACIONES
     * =========================== */

    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    public function invitedBy()
    {
        return $this->belongsTo(User::class, 'invited_by_user_id');
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }
}