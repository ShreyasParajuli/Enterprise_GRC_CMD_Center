# Enterprise GRC Command Center

Welcome to the Enterprise GRC (Governance, Risk, and Compliance) Command Center. This application is a PHP/MySQL-based platform designed to manage enterprise risks, assets, departments, and user roles within an organization.

## New Functions (Development Sprint — Day 3, Phase 1)

**Enterprise Asset Inventory Foundation**
We have recently laid the database foundation for the new Enterprise Asset Inventory module. This phase includes:
- **Asset Categories Table**: A normalized catalog of asset classifications (e.g., Hardware, Software, Cloud Service, Server, Security Infrastructure).
- **Assets Table**: The primary repository for tracking enterprise assets, which includes fields for operational status, asset life cycle, business criticality (Low to Critical), and granular C-I-A (Confidentiality, Integrity, Availability) ratings on a scale of 1-5.
- **Realistic Seed Data**: Seed assets across multiple departments (e.g., Active Directory Server, Enterprise Firewall, Cloud Infrastructure, Corporate Website) are now populated for realistic visualization.
- **Additive Schema**: The new foundation integrates cleanly with existing `users` and `departments` structures without disrupting the stable Risk Management module. 

*(Note: The Asset UI and CRUD operations are not yet implemented in Phase 1; this phase focuses purely on database normalization and readiness for future risk-mapping features.)*

---

## How to Run on Localhost (XAMPP)

Since the project uses PHP and MySQL without external frameworks, it is built to run effortlessly on environments like XAMPP, WAMP, or natively configured Apache/Nginx.

### 1. Requirements
- PHP 8.0+
- MySQL or MariaDB
- A local web server (e.g., Apache via XAMPP)

### 2. Deployment Steps

1. **Clone/Place the Project Directory**
   Ensure the project folder is placed in your web server's document root. For XAMPP, the path should be:
   `C:\xampp\htdocs\Enterprise_GRC_CMD_Center`

2. **Start Services**
   Open the XAMPP Control Panel and start **Apache** and **MySQL**.

3. **Set Up the Database**
   Open phpMyAdmin (usually `http://localhost/phpmyadmin`) or your preferred MySQL client, and execute the SQL scripts in the following order to build the database (`grc_command_center`) and populate it with seed data:
   - First, run: `schema.sql` (Sets up the core database, roles, departments, and users)
   - Second, run: `risk_management_schema.sql` (Sets up the Risk Management module)
   - Third, run: `asset_inventory_schema.sql` (Sets up the new Enterprise Asset Inventory foundation)

4. **Access the Application**
   Open your web browser and navigate to:
   [http://localhost/Enterprise_GRC_CMD_Center](http://localhost/Enterprise_GRC_CMD_Center)

5. **Log In**
   You can use the default seeded administrator credentials to access the system:
   - **Username:** `admin`  
   - **Password:** `admin123`

   *(Other seeded users like `ciso`, `risk_manager`, and `auditor` also use the password `admin123`.)*

---

## Documentation

For deep-dive documentation on the data models, please refer to:
- `asset_inventory_database.md` (Explains the Asset structure and design decisions)
