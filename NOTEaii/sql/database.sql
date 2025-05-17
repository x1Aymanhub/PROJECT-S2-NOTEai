
CREATE DATABASE IF NOT EXISTS noteai_db;
USE noteai_db;


CREATE TABLE IF NOT EXISTS admin (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(50) NOT NULL,
    email VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL
) ENGINE=InnoDB;


INSERT INTO admin (name, email, password) VALUES 
('Admin', 'admin@noteai.com', 'admin123');

CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(50) NOT NULL,
    prenom VARCHAR(50) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    id_admin INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (id_admin) REFERENCES admin(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- Table des modules
CREATE TABLE IF NOT EXISTS modules (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100) NOT NULL,
    semestre INT NOT NULL,
    coefficient DECIMAL(4,2) DEFAULT 1.00
) ENGINE=InnoDB;

-- Table des descriptions des modules
CREATE TABLE IF NOT EXISTS module_descriptions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    module_id INT NOT NULL,
    description_text TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (module_id) REFERENCES modules(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Table de liaison entre utilisateurs et modules
CREATE TABLE IF NOT EXISTS user_modules (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    module_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (module_id) REFERENCES modules(id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_module (user_id, module_id)
) ENGINE=InnoDB;



-- Index pour améliorer les performances
CREATE INDEX idx_users_email ON users(email);
CREATE INDEX idx_modules_code ON modules(code);
CREATE INDEX idx_notes_utilisateur_id ON note(utilisateur_id);
CREATE INDEX idx_notes_module_id ON note(module_id);
CREATE INDEX idx_user_modules_user_id ON user_modules(user_id);
CREATE INDEX idx_user_modules_module_id ON user_modules(module_id);


