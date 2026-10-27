-- Enterprise GRC Command Center: Audit Management Schema
-- Database tables for Audits, Findings, and Control Checklists

USE grc_command_center;

-- 1. Create audits table
CREATE TABLE IF NOT EXISTS audits (
    id INT AUTO_INCREMENT PRIMARY KEY,
    audit_id VARCHAR(20) NOT NULL UNIQUE, -- e.g. AUD-2026-001
    title VARCHAR(255) NOT NULL,
    description TEXT,
    audit_type ENUM('Internal', 'External', 'Regulatory', 'Certification', 'Third-Party') DEFAULT 'Internal',
    lead_auditor_id INT NOT NULL,
    department_id INT NOT NULL,
    framework VARCHAR(100) NOT NULL, -- e.g. ISO/IEC 27001, SOC 2 Type II, NIST CSF, PCI-DSS
    scope TEXT,
    status ENUM('Planned', 'Fieldwork', 'Review', 'Finalized', 'Archived') DEFAULT 'Planned',
    start_date DATE NULL,
    end_date DATE NULL,
    report_rating ENUM('Satisfactory', 'Needs Improvement', 'Unsatisfactory', 'Pending Rating') DEFAULT 'Pending Rating',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (lead_auditor_id) REFERENCES users(id) ON DELETE RESTRICT,
    FOREIGN KEY (department_id) REFERENCES departments(id) ON DELETE RESTRICT,
    
    INDEX idx_audit_status (status),
    INDEX idx_audit_type (audit_type),
    INDEX idx_audit_framework (framework),
    INDEX idx_audit_department (department_id),
    INDEX idx_audit_auditor (lead_auditor_id)
);

-- 2. Create audit_findings table
CREATE TABLE IF NOT EXISTS audit_findings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    finding_id VARCHAR(20) NOT NULL UNIQUE, -- e.g. FND-001
    audit_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    severity ENUM('Low', 'Medium', 'High', 'Critical') DEFAULT 'Medium',
    risk_id INT NULL,
    asset_id INT NULL,
    status ENUM('Open', 'Under Remediation', 'Pending Verification', 'Closed') DEFAULT 'Open',
    remediation_plan TEXT,
    due_date DATE NULL,
    assigned_to INT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (audit_id) REFERENCES audits(id) ON DELETE CASCADE,
    FOREIGN KEY (risk_id) REFERENCES risks(id) ON DELETE SET NULL,
    FOREIGN KEY (asset_id) REFERENCES assets(id) ON DELETE SET NULL,
    FOREIGN KEY (assigned_to) REFERENCES users(id) ON DELETE SET NULL,
    
    INDEX idx_finding_severity (severity),
    INDEX idx_finding_status (status),
    INDEX idx_finding_audit (audit_id)
);

-- 3. Create audit_checklists table (Control verification matrix)
CREATE TABLE IF NOT EXISTS audit_checklists (
    id INT AUTO_INCREMENT PRIMARY KEY,
    audit_id INT NOT NULL,
    control_code VARCHAR(50) NOT NULL,
    control_title VARCHAR(255) NOT NULL,
    status ENUM('Compliant', 'Non-Compliant', 'Partially Compliant', 'Pending') DEFAULT 'Pending',
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (audit_id) REFERENCES audits(id) ON DELETE CASCADE,
    INDEX idx_checklist_status (status)
);

-- Seed Data: Sample Enterprise Audits
INSERT IGNORE INTO audits (id, audit_id, title, description, audit_type, lead_auditor_id, department_id, framework, scope, status, start_date, end_date, report_rating) VALUES
(1, 'AUD-2026-001', 'ISO/IEC 27001 Annual Surveillance Audit', 'Annual formal surveillance audit evaluating ISMS governance, asset access controls, and incident response readiness.', 'Certification', 1, 1, 'ISO/IEC 27001', 'All corporate IT production networks, cloud VPCs, and authentication endpoints.', 'Fieldwork', '2026-09-15', '2026-10-30', 'Pending Rating'),
(2, 'AUD-2026-002', 'SOC 2 Type II Security & Confidentiality Audit', 'Independent third-party attestation of trust services criteria across all enterprise infrastructure.', 'External', 1, 1, 'SOC 2 Type II', 'Core SaaS infrastructure, database encryption, customer multi-tenancy, and CI/CD pipelines.', 'Review', '2026-08-01', '2026-10-10', 'Needs Improvement'),
(3, 'AUD-2026-003', 'Q3 Internal Access & Privileged Identity Audit', 'Internal review of privileged access management, orphaned accounts, and multi-factor enforcement.', 'Internal', 1, 2, 'NIST CSF v2.0', 'Active Directory, cloud IAM roles, VPN access, and service account keys.', 'Finalized', '2026-07-01', '2026-08-15', 'Satisfactory'),
(4, 'AUD-2026-004', 'PCI-DSS v4.0 Payment Card Data Security Review', 'Validation of point-of-sale tokenization and cardholder data environment segmentation.', 'Regulatory', 1, 1, 'PCI-DSS v4.0', 'Cardholder Data Environment (CDE), payment gateways, and network perimeter firewalls.', 'Planned', '2026-11-01', '2026-12-15', 'Pending Rating'),
(5, 'AUD-2026-005', 'Third-Party Vendor Cloud Security Audit', 'Security assessment of Tier-1 external vendor hosting critical customer databases.', 'Third-Party', 1, 2, 'ISO/IEC 27001', 'External hosting provider SOC reports, SLA compliance, and data sovereignty.', 'Finalized', '2026-06-01', '2026-06-30', 'Satisfactory');

-- Seed Data: Sample Findings
INSERT IGNORE INTO audit_findings (id, finding_id, audit_id, title, description, severity, risk_id, asset_id, status, remediation_plan, due_date, assigned_to) VALUES
(1, 'FND-2026-001', 1, 'Unrestricted S3 Bucket Public Access Permissions', 'Audit sample revealed an AWS S3 bucket containing application telemetry lacked block public access setting.', 'Critical', 4, 1, 'Under Remediation', 'Apply AWS Organizations SCP to enforce S3 Block Public Access across all accounts.', '2026-10-25', 1),
(2, 'FND-2026-002', 1, 'MFA Incomplete for Legacy Internal VPN Profiles', '8 legacy user profiles were discovered accessing internal tools without hardware/TOTP multi-factor challenge.', 'High', 6, 2, 'Open', 'Migrate all remaining VPN profiles to central Okta SSO with required FIDO2/MFA push.', '2026-11-05', 1),
(3, 'FND-2026-003', 2, 'Quarterly User Access Reviews Delayed Beyond 90 Days', 'Departmental user access certifications for Q2 were submitted 18 days after the compliance deadline.', 'Medium', 3, NULL, 'Pending Verification', 'Automate quarterly access review campaigns using GRC workflows with automated escalation.', '2026-10-20', 1),
(4, 'FND-2026-004', 2, 'Database Backup Encryption Key Rotation Lacked Proof', 'Database backups at rest were encrypted, but documentation for annual KMS key rotation could not be produced.', 'Medium', NULL, 1, 'Under Remediation', 'Configure automatic annual key rotation in AWS KMS and export CloudTrail audit records.', '2026-11-15', 1),
(5, 'FND-2026-005', 3, 'Terminated Employee Active Directory Account Not Disabled', 'An account for a contractor terminated 14 days ago was found active, though no logins occurred post-termination.', 'High', 3, NULL, 'Closed', 'Account immediately disabled. HR to IT webhook integration established for instant offboarding.', '2026-08-05', 1);

-- Seed Data: Sample Checklist Items
INSERT IGNORE INTO audit_checklists (id, audit_id, control_code, control_title, status, notes) VALUES
(1, 1, 'A.9.2.1', 'User Registration and De-registration', 'Partially Compliant', 'Automated onboarding works well; offboarding manual review has minor delays.'),
(2, 1, 'A.10.1.1', 'Cryptographic Controls Policy', 'Compliant', 'TLS 1.3 enforced on all perimeter endpoints; AES-256 for data at rest.'),
(3, 1, 'A.12.6.1', 'Management of Technical Vulnerabilities', 'Compliant', 'Weekly Tenable scans executed across all subnets with tickets assigned in JIRA.'),
(4, 1, 'A.13.1.1', 'Network Controls & Segmentation', 'Compliant', 'VPCs segmented with strict security groups and transit gateway inspection.'),
(5, 2, 'CC6.1', 'Logical Access Security & Identity', 'Partially Compliant', 'Quarterly certifications missed timeliness SLA as noted in FND-2026-003.'),
(6, 2, 'CC6.6', 'Boundary Protection & Firewalls', 'Compliant', 'WAF policies active and blocking OWASP Top 10 exploits.'),
(7, 2, 'CC7.2', 'Security Incident Response Program', 'Compliant', 'Annual tabletop exercise completed and documented with executive summary.'),
(8, 3, 'PR.AC-1', 'Identities and Credentials Management', 'Compliant', 'Centralized IAM directory in place with SSO integration.');
