CREATE DATABASE IF NOT EXISTS grc_command_center CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE grc_command_center;

CREATE TABLE IF NOT EXISTS roles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL UNIQUE,
    permissions_json JSON NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS departments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role_id INT NULL,
    department_id INT NULL,
    status ENUM('active', 'inactive', 'locked') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE SET NULL,
    FOREIGN KEY (department_id) REFERENCES departments(id) ON DELETE SET NULL
);

CREATE TABLE IF NOT EXISTS activity_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NULL,
    action VARCHAR(255) NOT NULL,
    ip_address VARCHAR(45) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);

-- Insert Default Roles
INSERT IGNORE INTO roles (name) VALUES 
('Admin'), 
('CISO'), 
('Risk Manager'), 
('Auditor');

-- Insert Default Department
INSERT IGNORE INTO departments (name) VALUES 
('IT Security'),
('Compliance'),
('Executive');

-- Insert Default Admin User (Password: admin123)
-- Hash generated via password_hash('admin123', PASSWORD_DEFAULT)
INSERT IGNORE INTO users (username, email, password_hash, role_id, department_id, status) VALUES 
('admin', 'admin@grc.local', '$2y$10$.4/lwmMzRdtmE3jzrBOe2.M10cgkrrK16IsjN8Vox1Lata5VzqbHS', 1, 1, 'active');
