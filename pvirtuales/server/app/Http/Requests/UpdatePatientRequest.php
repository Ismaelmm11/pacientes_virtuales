<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePatientRequest extends FormRequest
{
    /**
     * Determina si el usuario está autorizado a hacer esta petición
     */
    public function authorize(): bool
    {
        return auth()->check();
    }

    /**
     * Reglas de validación para la creación de pacientes
     */
    public function rules(): array
    {
        return [
            // Información del caso
            'case_title' => 'required|string|max:255',
            'learning_objectives' => 'nullable|string|max:1000',
            'subject_id' => 'required|integer|exists:subjects,id',
            'attendee_type' => 'required|string|in:patient,companion',
            'puede_inventar_datos_medicos' => 'nullable|boolean',
            // Campos del acompañante (requeridos solo si attendee_type=companion)
            'companion_name' => 'required_if:attendee_type,companion|nullable|string|max:200',
            'companion_relation' => 'required_if:attendee_type,companion|nullable|string',
            'companion_age' => 'nullable|integer|min:14|max:100',
            'companion_gender' => 'nullable|string|in:masculino,femenino,otro',
            'patient_description' => 'required|string|max:255',

            // Identidad del paciente
            'patient_name' => 'required|string|max:150',
            'patient_age' => 'required|integer|min:0|max:120',
            'patient_gender' => 'required|string|in:masculino,femenino,otro',
            'occupation' => 'nullable|string|max:150',
            'education_level' => 'nullable|string|in:sin_estudios,primaria,secundaria,bachillerato,universitario,postgrado',
            'personal_context' => 'nullable|string|max:1000',


            // Cuadro clínico
            'symptoms' => 'nullable|array|max:10',
            'symptoms.*.name' => 'nullable|string|max:200',
            'symptoms.*.reveal' => 'nullable|string|in:espontaneo,pregunta,oculta,miente,exagera',
            'symptoms.*.lie_text' => 'nullable|string|max:300',
            'medical_history' => 'nullable|string|max:2000',
            'current_medications' => 'nullable|string|max:1000',
            'real_diagnosis' => 'required|string|max:300',
            'frase_inicial' => 'required_if:mode,basic|nullable|string|max:1000',
            'motivo_consulta' => 'required_if:mode,basic|nullable|string|max:1000',

            // Medicamentos
            'medications' => 'nullable|array',
            'medications.*.name' => 'nullable|string|max:200',
            'medications.*.dose' => 'nullable|string|max:200',
            'medications.*.frequency' => 'nullable|string|max:200',
            'medications.*.adherence' => 'nullable|string|in:total,parcial,nula',
            'medications.*.adherence_detail' => 'nullable|string|max:500',
            'medications.*.reveal' => 'nullable|string|in:espontaneo,pregunta,oculta,miente',
            'medications.*.lie_text' => 'nullable|string|max:300',
            // Vicios
            'vices' => 'nullable|array',
            'vices.*.name' => 'nullable|string|max:200',
            'vices.*.reveal' => 'nullable|string|in:espontaneo,pregunta,oculta,miente',
            'vices.*.lie_text' => 'nullable|string|max:300',
            // Antecedentes familiares
            'family_history' => 'nullable|string|max:2000',

            // Personalidad y comportamiento
            'personality_type' => 'required|string|in:colaborador,ansioso,reservado,demandante,minimizador,hipocondriaco,agresivo,deprimido,desconfiado,confuso,evasivo',
            'verbosity_level' => 'required|integer|min:1|max:5',
            'medical_knowledge' => 'required|integer|min:1|max:5',
            'communication_style' => 'nullable|string|max:1000',
            'hidden_concerns' => 'nullable|string|max:1000',

            // Configuración adicional
            'special_instructions' => 'nullable|string|max:2000',
            'frases_limite' => 'required|array|max:5',
            'frases_limite.*' => 'required|string|max:200',
            'ejemplo_coherencia' => 'required|array',
            'ejemplo_coherencia.pregunta' => 'required|string|max:300',
            'ejemplo_coherencia.coherente' => 'required|string|max:300',
            'ejemplo_coherencia.incoherente' => 'required|string|max:300',
            'verbosity_custom' => 'nullable|string|max:500',
            'knowledge_custom' => 'nullable|string|max:500',
        ];
    }

    /**
     * Mensajes de error personalizados en español
     */
    public function messages(): array
    {
        return [
            // Caso
            'case_title.required' => 'El título del caso es obligatorio.',
            'case_title.max' => 'El título no puede exceder 255 caracteres.',
            'learning_objectives.max' => 'Los objetivos de aprendizaje no pueden exceder 1000 caracteres.',

            // Paciente
            'patient_name.required' => 'El nombre del paciente es obligatorio.',
            'patient_name.max' => 'El nombre no puede exceder 150 caracteres.',
            'patient_age.required' => 'La edad del paciente es obligatoria.',
            'patient_age.integer' => 'La edad debe ser un número entero.',
            'patient_age.min' => 'La edad no puede ser negativa.',
            'patient_age.max' => 'La edad no puede exceder 120 años.',
            'patient_gender.required' => 'El género del paciente es obligatorio.',
            'patient_gender.in' => 'El género seleccionado no es válido.',
            'occupation.max' => 'La ocupación no puede exceder 150 caracteres.',
            'education_level.in' => 'El nivel educativo seleccionado no es válido.',
            'personal_context.max' => 'El contexto personal no puede exceder 1000 caracteres.',

            // Clínico
            'symptoms.max' => 'No puedes añadir más de 10 síntomas.',
            'symptoms.*.name.max' => 'El nombre del síntoma no puede exceder 200 caracteres.',
            'symptoms.*.reveal.in' => 'El tipo de revelación del síntoma no es válido.',
            'medical_history.max' => 'Los antecedentes médicos no pueden exceder 2000 caracteres.',
            'current_medications.max' => 'La medicación actual no puede exceder 1000 caracteres.',
            'real_diagnosis.required' => 'El diagnóstico real es obligatorio.',
            'real_diagnosis.max' => 'El diagnóstico no puede exceder 300 caracteres.',
            
            // Personalidad
            'personality_type.required' => 'Debes seleccionar un tipo de personalidad.',
            'personality_type.in' => 'El tipo de personalidad seleccionado no es válido.',
            'verbosity_level.required' => 'El nivel de verbosidad es obligatorio.',
            'verbosity_level.integer' => 'El nivel de verbosidad debe ser un número.',
            'verbosity_level.min' => 'El nivel de verbosidad debe ser al menos 1.',
            'verbosity_level.max' => 'El nivel de verbosidad no puede exceder 5.',
            'medical_knowledge.required' => 'El nivel de conocimiento médico es obligatorio.',
            'medical_knowledge.integer' => 'El nivel de conocimiento médico debe ser un número.',
            'medical_knowledge.min' => 'El nivel de conocimiento médico debe ser al menos 1.',
            'medical_knowledge.max' => 'El nivel de conocimiento médico no puede exceder 5.',
            'communication_style.max' => 'El estilo de comunicación no puede exceder 1000 caracteres.',
            'hidden_concerns.max' => 'Las preocupaciones ocultas no pueden exceder 1000 caracteres.',

            // Adicional
            'special_instructions.max' => 'Las instrucciones especiales no pueden exceder 2000 caracteres.',
            'frase_inicial.required_if' => 'La frase inicial del paciente es obligatoria.',
            'frase_inicial.max' => 'La frase inicial no puede exceder 1000 caracteres.',
            'motivo_consulta.required_if' => 'El motivo de consulta es obligatorio.',
            'motivo_consulta.max' => 'El motivo de consulta no puede exceder 1000 caracteres.',
            'patient_description.max' => 'La descripción no puede exceder 255 caracteres.',
            'frases_limite.max' => 'No puedes añadir más de 5 frases de límite.',
            'frases_limite.*.max' => 'Cada frase de límite no puede exceder 200 caracteres.',
            'ejemplo_coherencia.pregunta.max' => 'La pregunta del ejemplo no puede exceder 300 caracteres.',
            'ejemplo_coherencia.coherente.max' => 'La respuesta coherente no puede exceder 300 caracteres.',
            'ejemplo_coherencia.incoherente.max' => 'La respuesta incoherente no puede exceder 300 caracteres.',

            'patient_description.required' => 'La descripción del caso es obligatoria.',
            'frases_limite.required' => 'Debes añadir al menos una frase de límite.',
            'frases_limite.min' => 'Debes añadir al menos una frase de límite.',
            'ejemplo_coherencia.pregunta.required' => 'La pregunta del ejemplo de coherencia es obligatoria.',
            'ejemplo_coherencia.coherente.required' => 'La respuesta coherente es obligatoria.',
            'ejemplo_coherencia.incoherente.required' => 'La respuesta incoherente es obligatoria.',
            'verbosity_custom' => 'descripción de verbosidad personalizada',
            'knowledge_custom' => 'descripción de conocimiento médico personalizada',

            'subject_id.required' => 'Debes seleccionar una asignatura.',
            'subject_id.exists' => 'La asignatura seleccionada no existe.',
            'companion_name.required_if' => 'El nombre del acompañante es obligatorio.',
            'companion_relation.required_if' => 'La relación del acompañante es obligatoria.',
        ];
    }

    /**
     * Atributos personalizados para los mensajes de error
     */
    public function attributes(): array
    {
        return [
            'case_title' => 'título del caso',
            'learning_objectives' => 'objetivos de aprendizaje',
            'patient_name' => 'nombre del paciente',
            'patient_age' => 'edad del paciente',
            'patient_gender' => 'género del paciente',
            'occupation' => 'ocupación',
            'education_level' => 'nivel educativo',
            'personal_context' => 'contexto personal',
            'symptoms' => 'síntomas',
            'medical_history' => 'antecedentes médicos',
            'current_medications' => 'medicación actual',
            'real_diagnosis' => 'diagnóstico real',
            'personality_type' => 'tipo de personalidad',
            'verbosity_level' => 'nivel de verbosidad',
            'medical_knowledge' => 'conocimiento médico',
            'communication_style' => 'estilo de comunicación',
            'hidden_concerns' => 'preocupaciones ocultas',
            'special_instructions' => 'instrucciones especiales',
            'patient_description' => 'descripción del caso',
            'frase_inicial' => 'frase inicial del paciente',
            'motivo_consulta' => 'motivo de consulta',
            'frases_limite' => 'frases de límite',
            'ejemplo_coherencia.pregunta' => 'pregunta del ejemplo',
            'ejemplo_coherencia.coherente' => 'respuesta coherente',
            'ejemplo_coherencia.incoherente' => 'respuesta incoherente',
            'subject_id' => 'asignatura',
            'attendee_type' => 'tipo de consulta',
            'companion_name' => 'nombre del acompañante',
            'companion_relation' => 'relación del acompañante',
        ];
    }

    /**
     * Preparar los datos antes de la validación
     */
    protected function prepareForValidation(): void
    {
        // Limpiar síntomas vacíos
        if ($this->has('symptoms')) {
            $symptoms = array_filter($this->symptoms, function ($symptom) {
                return !empty($symptom['name']);
            });
            $this->merge(['symptoms' => array_values($symptoms)]);
        }
        // Limpiar frases límite vacías
        if ($this->has('frases_limite')) {
            $frases = array_filter($this->frases_limite ?? [], fn($f) => !empty(trim($f)));
            $this->merge(['frases_limite' => array_values($frases)]);
        }
    }
}