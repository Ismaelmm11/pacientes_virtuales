<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Prompt compilado y versionado para la IA.
 * 
 * Almacena el prompt Markdown generado desde las tablas intermedias,
 * con soporte para versionado y edición manual por el profesor.
 *
 * @property int $id
 * @property int $patient_id
 * @property string|null $subtitulo Descripción breve del caso clínico.
 * @property string $prompt_content El prompt Markdown completo.
 * @property \Carbon\Carbon|null $generated_at Cuándo se generó automáticamente.
 * @property \Carbon\Carbon|null $last_edited_at Última edición manual.
 * @property int|null $edited_by_user_id Quién lo editó manualmente.
 * @property int $version Número de versión del prompt.
 * @property bool $is_manually_edited Si fue editado a mano tras generarse.
 * @property string|null $source_data_hash Hash de los datos fuente para detectar cambios.
 */
class PatientPrompt extends Model
{
    use HasFactory;

    const UPDATED_AT = null;

    protected $table = 'patient_prompts';

    protected $fillable = [
        'patient_id',
        'subtitulo',
        'prompt_content',
        'generated_at',
        'last_edited_at',
        'edited_by_user_id',
        'version',
        'is_manually_edited',
        'source_data_hash',
    ];

    protected $casts = [
        'generated_at'       => 'datetime',
        'last_edited_at'     => 'datetime',
        'is_manually_edited' => 'boolean',
    ];

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    public function editor()
    {
        return $this->belongsTo(User::class, 'edited_by_user_id');
    }
}