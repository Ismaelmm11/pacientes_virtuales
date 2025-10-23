-- ========= Tabla Roles =========
-- Esta tabla debe crearse primero porque la tabla 'users' depende de ella.

CREATE TABLE roles (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Poblamos la tabla con los roles iniciales
INSERT INTO roles (name) VALUES ('STUDENT'), ('TEACHER'), ('ADMIN');


-- ========= Tabla Usuarios =========
-- Contiene la información del usuario y se conecta con la tabla 'roles'.

CREATE TABLE users (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(150) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NULL, -- Opcional para permitir inicios de sesión con redes sociales (ej. Google)
    birth_date DATE NULL,
    gender ENUM('MALE', 'FEMALE', 'OTHER') NULL,
    auth_provider VARCHAR(50) NOT NULL DEFAULT 'local', -- Identifica el método de login ('local' o 'google')
    role_id INT UNSIGNED NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    -- Define la relación con la tabla 'roles'
    FOREIGN KEY (role_id) REFERENCES roles(id)
) ENGINE=InnoDB;

