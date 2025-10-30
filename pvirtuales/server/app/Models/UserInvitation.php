<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserInvitation extends Model
{
    use HasFactory;

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