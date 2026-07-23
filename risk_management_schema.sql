-- Phase 1: Database Design (Risk Management Foundation)

-- 1. Create risk_categories table
CREATE TABLE IF NOT EXISTS risk_categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    category_name VARCHAR(100) NOT NULL UNIQUE,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Insert seed data for risk_categories
INSERT IGNORE INTO risk_categories (category_name, description) VALUES
('Cybersecurity', 'Risks related to digital assets, IT infrastructure, and data breaches.'),
('Operational', 'Risks arising from failed internal processes, people, and systems.'),
('Financial', 'Risks impacting the financial health and stability of the organization.'),
('Strategic', 'Risks affecting the long-term business strategy and competitive advantage.'),
('Compliance', 'Risks related to regulatory adherence and legal obligations.'),
('Third-Party', 'Risks introduced by vendors, suppliers, and external partners.'),
('Physical Security', 'Risks related to physical premises, assets, and personnel safety.');

-- 2. Create risks table
-- Note: Risk score is not stored as it is derived via (likelihood * impact)
CREATE TABLE IF NOT EXISTS risks (
    id INT AUTO_INCREMENT PRIMARY KEY,
    risk_id VARCHAR(20) NOT NULL UNIQUE, -- e.g. RSK-0001
    title VARCHAR(255) NOT NULL,
    description TEXT,
    category_id INT NOT NULL,
    department_id INT NOT NULL,
    owner_id INT NOT NULL,
    likelihood INT NOT NULL CHECK (likelihood BETWEEN 1 AND 5),
    impact INT NOT NULL CHECK (impact BETWEEN 1 AND 5),
    treatment_strategy ENUM('Mitigate', 'Transfer', 'Avoid', 'Accept') DEFAULT 'Mitigate',
    status ENUM('Open', 'In Progress', 'Closed', 'Archived') DEFAULT 'Open',
    review_date DATE NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Foreign Key Relationships
    FOREIGN KEY (category_id) REFERENCES risk_categories(id) ON DELETE RESTRICT,
    FOREIGN KEY (department_id) REFERENCES departments(id) ON DELETE RESTRICT,
    FOREIGN KEY (owner_id) REFERENCES users(id) ON DELETE RESTRICT,
    
    -- Indexes for searchable and filterable fields
    INDEX idx_risk_status (status),
    INDEX idx_risk_owner (owner_id),
    INDEX idx_risk_department (department_id),
    INDEX idx_risk_category (category_id)
);

-- Insert seed data for risks
-- Using department 1 (IT Security), 2 (Compliance), 3 (Executive) and owner 1 (Admin) as per default schema
INSERT IGNORE INTO risks (risk_id, title, description, category_id, department_id, owner_id, likelihood, impact, treatment_strategy, status, review_date) VALUES
('RSK-0001', 'SQL Injection Vulnerability in Legacy Portal', 'Potential for attackers to execute arbitrary SQL commands via the login form.', 1, 1, 1, 3, 5, 'Mitigate', 'Open', '2026-08-01'),
('RSK-0002', 'Ransomware Attack on Corporate Network', 'Risk of malware encrypting critical files and demanding a ransom.', 1, 1, 1, 4, 5, 'Mitigate', 'In Progress', '2026-09-15'),
('RSK-0003', 'Insider Threat - Unauthorized Data Access', 'Employees with excessive permissions might access sensitive data inappropriately.', 2, 2, 1, 2, 4, 'Mitigate', 'Open', '2026-10-01'),
('RSK-0004', 'Cloud Storage Misconfiguration', 'Publicly accessible S3 buckets exposing customer records.', 1, 1, 1, 3, 5, 'Mitigate', 'Closed', '2026-07-01'),
('RSK-0005', 'Third-Party Vendor Data Breach', 'A key supplier getting compromised, leading to a supply chain attack.', 6, 3, 1, 3, 4, 'Transfer', 'Open', '2026-11-20'),
('RSK-0006', 'Weak Password Policy', 'Employees using easily guessable passwords leading to account takeovers.', 1, 1, 1, 4, 3, 'Mitigate', 'In Progress', '2026-08-10'),
('RSK-0007', 'Non-Compliance with GDPR', 'Failure to properly manage consent and right-to-be-forgotten requests.', 5, 2, 1, 2, 5, 'Mitigate', 'Open', '2026-12-01');
