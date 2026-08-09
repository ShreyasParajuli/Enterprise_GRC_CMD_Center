-- Phase 1: Database Design (Enterprise Asset Inventory Foundation)
-- Run this script against the grc_command_center database after schema.sql

USE grc_command_center;

-- 1. Create asset_categories table
CREATE TABLE IF NOT EXISTS asset_categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    category_name VARCHAR(100) NOT NULL UNIQUE,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Insert seed data for asset_categories
INSERT IGNORE INTO asset_categories (category_name, description) VALUES
('Hardware', 'Physical computing equipment including servers, appliances, and peripherals.'),
('Software', 'Licensed applications, operating systems, and platform software.'),
('Cloud Service', 'SaaS, PaaS, and IaaS resources hosted by external providers.'),
('Network', 'Routers, switches, firewalls, VPN gateways, and load balancers.'),
('Database', 'Structured data stores supporting business and operational systems.'),
('Application', 'Internally developed or deployed business applications and web services.'),
('Endpoint', 'Laptops, desktops, mobile devices, and other user-facing workstations.'),
('Server', 'Physical or virtualized compute instances running on-premises or in private cloud.'),
('Security Infrastructure', 'Security-focused appliances, systems, and platforms.'),
('Business Service', 'High-level business services supported by underlying technology assets.');

-- 2. Create assets table
-- Note: Criticality is stored independently; CIA ratings (1-5) support future derived scoring
CREATE TABLE IF NOT EXISTS assets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    asset_id VARCHAR(20) NOT NULL UNIQUE, -- e.g. AST-0001
    asset_name VARCHAR(255) NOT NULL,
    description TEXT,
    category_id INT NOT NULL,
    asset_type VARCHAR(100) NOT NULL,
    owner_id INT NOT NULL,
    department_id INT NOT NULL,
    criticality ENUM('Low', 'Medium', 'High', 'Critical') DEFAULT 'Medium',
    confidentiality INT NOT NULL CHECK (confidentiality BETWEEN 1 AND 5),
    integrity INT NOT NULL CHECK (integrity BETWEEN 1 AND 5),
    availability INT NOT NULL CHECK (availability BETWEEN 1 AND 5),
    location VARCHAR(255) NULL,
    status ENUM('Active', 'Inactive', 'Maintenance', 'Retired', 'Pending Deployment') DEFAULT 'Active',
    purchase_date DATE NULL,
    warranty_expiry DATE NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    -- Foreign Key Relationships
    FOREIGN KEY (category_id) REFERENCES asset_categories(id) ON DELETE RESTRICT,
    FOREIGN KEY (owner_id) REFERENCES users(id) ON DELETE RESTRICT,
    FOREIGN KEY (department_id) REFERENCES departments(id) ON DELETE RESTRICT,

    -- Indexes for searchable and filterable fields
    INDEX idx_asset_status (status),
    INDEX idx_asset_owner (owner_id),
    INDEX idx_asset_department (department_id),
    INDEX idx_asset_category (category_id),
    INDEX idx_asset_criticality (criticality),
    INDEX idx_asset_type (asset_type)
);

-- Supplementary seed users for realistic owner diversity (Password: admin123)
INSERT IGNORE INTO users (username, email, password_hash, role_id, department_id, status)
SELECT 'ciso', 'ciso@grc.local', '$2y$10$.4/lwmMzRdtmE3jzrBOe2.M10cgkrrK16IsjN8Vox1Lata5VzqbHS', r.id, d.id, 'active'
FROM roles r, departments d
WHERE r.name = 'CISO' AND d.name = 'IT Security'
LIMIT 1;

INSERT IGNORE INTO users (username, email, password_hash, role_id, department_id, status)
SELECT 'risk_manager', 'risk@grc.local', '$2y$10$.4/lwmMzRdtmE3jzrBOe2.M10cgkrrK16IsjN8Vox1Lata5VzqbHS', r.id, d.id, 'active'
FROM roles r, departments d
WHERE r.name = 'Risk Manager' AND d.name = 'Compliance'
LIMIT 1;

INSERT IGNORE INTO users (username, email, password_hash, role_id, department_id, status)
SELECT 'auditor', 'auditor@grc.local', '$2y$10$.4/lwmMzRdtmE3jzrBOe2.M10cgkrrK16IsjN8Vox1Lata5VzqbHS', r.id, d.id, 'active'
FROM roles r, departments d
WHERE r.name = 'Auditor' AND d.name = 'Executive'
LIMIT 1;

-- Insert seed data for assets (references resolved by name, not hardcoded IDs)
INSERT IGNORE INTO assets (
    asset_id, asset_name, description, category_id, asset_type,
    owner_id, department_id, criticality,
    confidentiality, integrity, availability,
    location, status, purchase_date, warranty_expiry
)
SELECT 'AST-0001', 'Active Directory Server',
    'Primary domain controller providing identity and access management for the corporate network.',
    c.id, 'Domain Controller', u.id, d.id, 'Critical', 5, 5, 5,
    'Data Center A - Rack 12', 'Active', '2022-03-15', '2027-03-15'
FROM asset_categories c, users u, departments d
WHERE c.category_name = 'Server' AND u.username = 'ciso' AND d.name = 'IT Security'
LIMIT 1;

INSERT IGNORE INTO assets (
    asset_id, asset_name, description, category_id, asset_type,
    owner_id, department_id, criticality,
    confidentiality, integrity, availability,
    location, status, purchase_date, warranty_expiry
)
SELECT 'AST-0002', 'Finance Database',
    'Oracle database hosting general ledger, accounts payable, and financial reporting data.',
    c.id, 'Relational Database', u.id, d.id, 'Critical', 5, 5, 4,
    'Data Center A - DB Cluster 01', 'Active', '2021-06-01', '2026-06-01'
FROM asset_categories c, users u, departments d
WHERE c.category_name = 'Database' AND u.username = 'admin' AND d.name = 'Compliance'
LIMIT 1;

INSERT IGNORE INTO assets (
    asset_id, asset_name, description, category_id, asset_type,
    owner_id, department_id, criticality,
    confidentiality, integrity, availability,
    location, status, purchase_date, warranty_expiry
)
SELECT 'AST-0003', 'HR Management System',
    'Workday-based platform for employee records, payroll, and performance management.',
    c.id, 'Enterprise Application', u.id, d.id, 'High', 4, 4, 3,
    'Cloud Service - US-East Region', 'Active', '2023-01-10', '2026-01-10'
FROM asset_categories c, users u, departments d
WHERE c.category_name = 'Application' AND u.username = 'risk_manager' AND d.name = 'Executive'
LIMIT 1;

INSERT IGNORE INTO assets (
    asset_id, asset_name, description, category_id, asset_type,
    owner_id, department_id, criticality,
    confidentiality, integrity, availability,
    location, status, purchase_date, warranty_expiry
)
SELECT 'AST-0004', 'Corporate Website',
    'Public-facing marketing and investor relations website hosted on AWS.',
    c.id, 'Web Application', u.id, d.id, 'Medium', 2, 3, 4,
    'AWS - us-east-1', 'Active', '2020-09-20', NULL
FROM asset_categories c, users u, departments d
WHERE c.category_name = 'Application' AND u.username = 'auditor' AND d.name = 'Executive'
LIMIT 1;

INSERT IGNORE INTO assets (
    asset_id, asset_name, description, category_id, asset_type,
    owner_id, department_id, criticality,
    confidentiality, integrity, availability,
    location, status, purchase_date, warranty_expiry
)
SELECT 'AST-0005', 'VPN Gateway',
    'Palo Alto GlobalProtect gateway enabling secure remote access for employees.',
    c.id, 'VPN Appliance', u.id, d.id, 'Critical', 4, 5, 5,
    'DMZ - Network Rack 03', 'Active', '2022-11-05', '2027-11-05'
FROM asset_categories c, users u, departments d
WHERE c.category_name = 'Network' AND u.username = 'ciso' AND d.name = 'IT Security'
LIMIT 1;

INSERT IGNORE INTO assets (
    asset_id, asset_name, description, category_id, asset_type,
    owner_id, department_id, criticality,
    confidentiality, integrity, availability,
    location, status, purchase_date, warranty_expiry
)
SELECT 'AST-0006', 'SIEM Server',
    'Splunk Enterprise deployment aggregating security logs and enabling threat detection.',
    c.id, 'Security Platform', u.id, d.id, 'High', 4, 5, 4,
    'Data Center A - Rack 08', 'Active', '2023-04-18', '2028-04-18'
FROM asset_categories c, users u, departments d
WHERE c.category_name = 'Security Infrastructure' AND u.username = 'ciso' AND d.name = 'IT Security'
LIMIT 1;

INSERT IGNORE INTO assets (
    asset_id, asset_name, description, category_id, asset_type,
    owner_id, department_id, criticality,
    confidentiality, integrity, availability,
    location, status, purchase_date, warranty_expiry
)
SELECT 'AST-0007', 'Employee Laptop',
    'Standard-issue Dell Latitude laptop for general corporate use.',
    c.id, 'Laptop', u.id, d.id, 'Low', 3, 2, 2,
    'Remote - Assigned Pool', 'Active', '2025-01-08', '2028-01-08'
FROM asset_categories c, users u, departments d
WHERE c.category_name = 'Endpoint' AND u.username = 'risk_manager' AND d.name = 'Compliance'
LIMIT 1;

INSERT IGNORE INTO assets (
    asset_id, asset_name, description, category_id, asset_type,
    owner_id, department_id, criticality,
    confidentiality, integrity, availability,
    location, status, purchase_date, warranty_expiry
)
SELECT 'AST-0008', 'Enterprise Firewall',
    'Fortinet FortiGate perimeter firewall protecting the corporate network boundary.',
    c.id, 'Firewall', u.id, d.id, 'Critical', 4, 5, 5,
    'DMZ - Network Rack 01', 'Active', '2021-02-28', '2026-02-28'
FROM asset_categories c, users u, departments d
WHERE c.category_name = 'Security Infrastructure' AND u.username = 'ciso' AND d.name = 'IT Security'
LIMIT 1;

INSERT IGNORE INTO assets (
    asset_id, asset_name, description, category_id, asset_type,
    owner_id, department_id, criticality,
    confidentiality, integrity, availability,
    location, status, purchase_date, warranty_expiry
)
SELECT 'AST-0009', 'Email Infrastructure',
    'On-premises Exchange hybrid server handling internal mail routing and relay.',
    c.id, 'Mail Server', u.id, d.id, 'High', 4, 4, 5,
    'Data Center A - Rack 05', 'Maintenance', '2019-07-12', '2025-07-12'
FROM asset_categories c, users u, departments d
WHERE c.category_name = 'Application' AND u.username = 'admin' AND d.name = 'IT Security'
LIMIT 1;

INSERT IGNORE INTO assets (
    asset_id, asset_name, description, category_id, asset_type,
    owner_id, department_id, criticality,
    confidentiality, integrity, availability,
    location, status, purchase_date, warranty_expiry
)
SELECT 'AST-0010', 'Cloud Infrastructure',
    'AWS Cloud tenant providing foundational compute, storage, and networking.',
    c.id, 'IaaS Tenant', u.id, d.id, 'Critical', 5, 4, 5,
    'Cloud Service', 'Active', '2022-08-01', '2027-08-01'
FROM asset_categories c, users u, departments d
WHERE c.category_name = 'Cloud Service' AND u.username = 'ciso' AND d.name = 'IT Security'
LIMIT 1;

INSERT IGNORE INTO assets (
    asset_id, asset_name, description, category_id, asset_type,
    owner_id, department_id, criticality,
    confidentiality, integrity, availability,
    location, status, purchase_date, warranty_expiry
)
SELECT 'AST-0011', 'Production Web Server',
    'Nginx load balancer and web server for the primary customer portal.',
    c.id, 'Web Server', u.id, d.id, 'High', 3, 4, 5,
    'AWS - us-east-2', 'Active', '2021-11-20', '2026-11-20'
FROM asset_categories c, users u, departments d
WHERE c.category_name = 'Server' AND u.username = 'ciso' AND d.name = 'IT Security'
LIMIT 1;

INSERT IGNORE INTO assets (
    asset_id, asset_name, description, category_id, asset_type,
    owner_id, department_id, criticality,
    confidentiality, integrity, availability,
    location, status, purchase_date, warranty_expiry
)
SELECT 'AST-0012', 'Backup Server',
    'Veeam backup and replication server managing immutable backups.',
    c.id, 'Backup Appliance', u.id, d.id, 'Critical', 5, 5, 4,
    'Data Center B - Rack 02', 'Active', '2022-01-15', '2027-01-15'
FROM asset_categories c, users u, departments d
WHERE c.category_name = 'Server' AND u.username = 'admin' AND d.name = 'Compliance'
LIMIT 1;

INSERT IGNORE INTO assets (
    asset_id, asset_name, description, category_id, asset_type,
    owner_id, department_id, criticality,
    confidentiality, integrity, availability,
    location, status, purchase_date, warranty_expiry
)
SELECT 'AST-0013', 'Internal API Server',
    'Microservices API gateway for internal application communication.',
    c.id, 'API Gateway', u.id, d.id, 'High', 4, 4, 5,
    'AWS - us-east-1', 'Pending Deployment', '2024-05-10', '2029-05-10'
FROM asset_categories c, users u, departments d
WHERE c.category_name = 'Server' AND u.username = 'ciso' AND d.name = 'IT Security'
LIMIT 1;

INSERT IGNORE INTO assets (
    asset_id, asset_name, description, category_id, asset_type,
    owner_id, department_id, criticality,
    confidentiality, integrity, availability,
    location, status, purchase_date, warranty_expiry
)
SELECT 'AST-0014', 'Network Core Switch',
    'Cisco Nexus core switch routing primary data center traffic.',
    c.id, 'Switch', u.id, d.id, 'Critical', 2, 5, 5,
    'Data Center A - Network Core', 'Active', '2020-03-12', '2025-03-12'
FROM asset_categories c, users u, departments d
WHERE c.category_name = 'Network' AND u.username = 'ciso' AND d.name = 'IT Security'
LIMIT 1;

INSERT IGNORE INTO assets (
    asset_id, asset_name, description, category_id, asset_type,
    owner_id, department_id, criticality,
    confidentiality, integrity, availability,
    location, status, purchase_date, warranty_expiry
)
SELECT 'AST-0015', 'Security Monitoring Platform',
    'CrowdStrike Falcon management console for endpoint detection and response.',
    c.id, 'Security Platform', u.id, d.id, 'Critical', 5, 5, 5,
    'Cloud Service - Hosted', 'Active', '2023-08-20', '2026-08-20'
FROM asset_categories c, users u, departments d
WHERE c.category_name = 'Security Infrastructure' AND u.username = 'ciso' AND d.name = 'IT Security'
LIMIT 1;
