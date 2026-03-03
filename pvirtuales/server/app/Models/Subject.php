<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Representa una Asignatura o Especialidad Médica.
 * * Sirve para categorizar los casos clínicos (ej: Cardiología, Pediatría).
 *
 * @property int $id
 * @property string $name Nombre de la asignatura (ej: "Urgencias Pediátricas").
 * @property string|null $description Descripción opcional.
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Patient[] $patients
 */
class Subject extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $table = 'subjects';

    protected $fillable = [
        'name',
        'description'
    ];

    /**
     * Obtiene todos los pacientes/casos asociados a esta asignatura.
     */
    public function patients()
    {
        return $this->hasMany(Patient::class);
    }
}