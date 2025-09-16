-- ========= Tabla Asignaturas =========
-- Almacena las asignaturas o cursos a los que se asociarán los casos y los alumnos.

CREATE TABLE subjects (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    code VARCHAR(20) UNIQUE, -- El código es opcional pero debe ser único si existe
    institution VARCHAR(255) NULL, -- El centro o institución es opcional
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ========= Tabla de Unión Asignatura-Usuario =========
-- Conecta a los usuarios (alumnos y profesores) con las asignaturas.
-- Es la pieza clave para responder a todas tus preguntas.

CREATE TABLE subject_user (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    subject_id INT UNSIGNED NOT NULL,

    -- Aseguramos que un usuario no pueda estar dos veces en la misma asignatura
    UNIQUE KEY (user_id, subject_id),

    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (subject_id) REFERENCES subjects(id) ON DELETE CASCADE
) ENGINE=InnoDB;