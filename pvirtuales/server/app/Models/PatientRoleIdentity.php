<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Identidad y rol de quien habla en la consulta.
 * 
 * Puede ser el propio paciente o un acompañante (madre, padre, cuidador...)
 * que habla en nombre del paciente. Si es acompañante, la IA simula
 * al acompañante y el paciente NO interviene en la conversación.
 *
 * @property int $id
 * @property int $patient_id
 * @property bool $es_acompanante Si es TRUE, la IA simula al acompañante.
 * @property string|null $nombre_acompanante Nombre del acompañante.
 * @property string|null $relacion_con_paciente Ej: madre, padre, pareja, cuidador.
 * @property int|null $edad_acompanante Edad del acompañante.
 * @property string|null $genero_acompanante Género del acompañante.
 * @property string $rol_principal Frase que define al personaje para la IA.
 * @property string $datos_demograficos Edad, género, nivel educativo.
 * @property string $contexto_sociolaboral Ocupación, situación familiar.
 * @property string $nivel_conocimiento Cuánto sabe de medicina.
 * @property array|null $campos_custom Campos adicionales libres (JSON, solo avanzado).
 */
class PatientRoleIdentity extends Model
{
    use HasFactory;

    const UPDATED_AT = null;

    protected $table = 'patient_role_identity';

    protected $fillable = [
        'patient_id',
        'es_acompanante',
        'nombre_acompanante',
        'relacion_con_paciente',
        'edad_acompanante',
        'genero_acompanante',
        'rol_principal',
        'datos_demograficos',
        'contexto_sociolaboral',
        'nivel_conocimiento',
        'campos_custom',
    ];

    protected $casts = [
        'es_acompanante' => 'boolean',
        'campos_custom'  => 'array',
    ];

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }
}