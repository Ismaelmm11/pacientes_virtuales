-- ========= Tabla Tipos de Paciente =========
-- Define las categorías a las que puede pertenecer un paciente.

CREATE TABLE patient_types (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ========= Tabla Pacientes =========
-- El corazón del proyecto. Define cada caso clínico virtual.

CREATE TABLE patients (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    case_title VARCHAR(255) NOT NULL,
    role_description TEXT NULL,
    personality TEXT NULL,
    interaction_rules TEXT NULL,
    initial_phrase TEXT NULL,
    specific_data JSON NULL,
    created_by_user_id INT UNSIGNED NOT NULL,
    subject_id INT UNSIGNED NOT NULL,
    patient_type_id INT UNSIGNED NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    -- Definimos las relaciones
    FOREIGN KEY (created_by_user_id) REFERENCES users(id),
    FOREIGN KEY (subject_id) REFERENCES subjects(id),
    FOREIGN KEY (patient_type_id) REFERENCES patient_types(id)
) ENGINE=InnoDB;

-- ========= Tabla Perfiles de Campos =========
-- Define las plantillas de campos para cada tipo de paciente. Es el motor de los formularios dinámicos.

CREATE TABLE field_profiles (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    patient_type_id INT UNSIGNED NOT NULL,
    field_name VARCHAR(100) NOT NULL,
    form_label VARCHAR(255) NOT NULL,
    form_type ENUM('textarea', 'input', 'header') NOT NULL DEFAULT 'textarea', -- 'header' para títulos de sección
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    -- Aseguramos que el nombre técnico del campo sea único para cada tipo de paciente
    UNIQUE KEY (patient_type_id, field_name),

    -- Definimos la relación con la tabla de tipos de paciente
    FOREIGN KEY (patient_type_id) REFERENCES patient_types(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ========= Tabla Información Extra =========
-- Almacena el "expediente clínico" que el alumno revisa antes de la simulación.

CREATE TABLE extra_information (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    patient_id INT UNSIGNED NOT NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT NULL, -- Para datos clínicos en texto
    file_url VARCHAR(255) NULL, -- Para enlaces a archivos (PDFs, imágenes)

    -- Definimos las relaciones
    FOREIGN KEY (patient_id) REFERENCES patients(id) ON DELETE CASCADE,
) ENGINE=InnoDB;