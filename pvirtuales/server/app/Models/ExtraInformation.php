<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Modelo que representa información adicional relacionada con un paciente virtual, que no encaja en las categorías principales pero que es relevante para la simulación clínica.
 * 
 * A cada paciente virtual puede tener múltiples registros de información extra que proporcionan contexto adicional, recursos o detalles específicos que enriquecen la experiencia de aprendizaje del estudiante.
 *
 * @property int $id
 * @property int $patient_id ID que identifica al paciente asociado a la información extra.
 * @property string $title Título de la información extra.
 * @property string|null $description Descripción de la información extra.
 * @property string|null $file_url URL del archivo asociado a la información extra, si aplica.
 * @property int $info_order Orden de la información extra dentro del paciente virtual.ada.
 * @property \Carbon\Carbon $created_at
 */
class ExtraInformation extends Model
{

    protected $table = 'extra_information';

    /**
     * La tabla no usa updated_at, solo created_at
     */
    const UPDATED_AT = null;

    protected $fillable = [
        'patient_id',
        'title',
        'type',
        'description',
        'file_url',
        'info_order',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    /* ===========================
     * RELACIONES
     * =========================== */

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

}