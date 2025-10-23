-- ========= Tabla Intentos de Test =========
-- Registra cada simulación completada por un alumno.

CREATE TABLE test_attempts (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    patient_id INT UNSIGNED NOT NULL,
    interview_transcript JSON NULL,
    final_score DECIMAL(5, 2) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (patient_id) REFERENCES patients(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ========= Tabla Preguntas (Versión Final) =========
-- El banco de preguntas, incluyendo su puntuación y feedback automático.

CREATE TABLE questions (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    patient_id INT UNSIGNED NOT NULL,
    question_text TEXT NOT NULL,
    question_type ENUM('MULTIPLE_CHOICE', 'OPEN_ENDED', 'TRUE_FALSE') NOT NULL,
    options JSON NULL,
    correct_answer TEXT NULL,
    points DECIMAL(5, 2) NOT NULL DEFAULT 10.00, -- Puntuación por defecto de la pregunta
    feedback_correct TEXT NULL, -- Feedback si la respuesta es correcta
    feedback_incorrect TEXT NULL, -- Feedback si la respuesta es incorrecta
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (patient_id) REFERENCES patients(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ========= Tabla Respuestas =========
-- Almacena la respuesta del alumno, la puntuación que obtuvo y el feedback final.

CREATE TABLE answers (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    test_attempt_id INT UNSIGNED NOT NULL,
    question_id INT UNSIGNED NOT NULL,
    given_answer TEXT NOT NULL,
    is_correct BOOLEAN NULL,
    score DECIMAL(5, 2) NULL, -- La puntuación obtenida por el alumno
    feedback TEXT NULL, -- El feedback final (automático o del profesor)

    FOREIGN KEY (test_attempt_id) REFERENCES test_attempts(id) ON DELETE CASCADE,
    FOREIGN KEY (question_id) REFERENCES questions(id) ON DELETE CASCADE
) ENGINE=InnoDB;