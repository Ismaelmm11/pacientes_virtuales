<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Role extends Model
{
    use HasFactory;

    /**
     * Deshabilitamos 'updated_at' porque no existe en la tabla
     */
    const UPDATED_AT = null;

    /**
     * Los atributos que se pueden asignar masivamente
     * (Aunque no se crearán roles nuevos, es buena práctica definirlo)
     */
    protected $fillable = [
        'name'
    ];

    /**
     * ============================================
     * CONSTANTES DE ROLES FIJOS
     * ============================================
     * Estos IDs corresponden a los registros existentes en la BD
     * y NO deben cambiar nunca.
     */
    const STUDENT_ID = 1;
    const TEACHER_ID = 2;
    const ADMIN_ID = 3;

    /**
     * ============================================
     * RELACIONES
     * ============================================
     */
    
    /**
     * Un rol tiene muchos usuarios
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }
}