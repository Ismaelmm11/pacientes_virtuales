<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Modelo para gestionar los tokens de invitación de nuevos usuarios.
 * * @property int $id
 * @property string $email Correo electrónico al que se envía la invitación.
 * @property string $token Identificador único para el enlace de registro.
 */

class UserInvitation extends Model
{
    use HasFactory;

    public $timestamps = false;

    // Le decimos a Laravel que NO use la columna 'updated_at'
    const UPDATED_AT = null;

    /**
     * El nombre de la tabla.
     */
    protected $table = 'user_invitations';

    /**
     * Los campos que permitimos rellenar.
     */
    protected $fillable = [
        'email',
        'token',
    ];
}