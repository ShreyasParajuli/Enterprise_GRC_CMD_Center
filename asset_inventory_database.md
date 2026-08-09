# Enterprise Asset Inventory — Database Documentation

**Phase 1 of 8 · Day 3 · Database Foundation**

This document describes the database schema introduced in `asset_inventory_schema.sql`. Run that script against the `grc_command_center` database after `schema.sql`.

---

## Tables

### `asset_categories`

**Purpose:** Provides a normalized lookup of asset classification types (Hardware, Software, Cloud Service, etc.). Separating categories from the main `assets` table keeps classification consistent across the enterprise, supports filtering and reporting, and allows future modules (Controls, Audits, Compliance) to reference category-level policies without duplicating strings on every asset row.

| Column         | Type         | Notes                              |
|----------------|--------------|------------------------------------|
| id             | INT PK       | Auto-increment                     |
| category_name  | VARCHAR(100) | Unique display name                |
| description    | TEXT         | Optional category description      |
| created_at     | TIMESTAMP    | Record creation time               |
| updated_at     | TIMESTAMP    | Last modification time             |

**Seed categories:** Hardware, Software, Cloud Service, Network, Database, Application, Endpoint, Server, Security Infrastructure, Business Service.

---

### `assets`

**Purpose:** The core enterprise asset register. Each row represents a technology asset that can later be linked to Risks, Controls, Audits, Evidence, and Compliance requirements. The table captures ownership, organizational context, operational status, lifecycle dates, and security classification attributes needed for GRC workflows.

| Column          | Type          | Notes                                           |
|-----------------|---------------|-------------------------------------------------|
| id              | INT PK        | Internal surrogate key                          |
| asset_id        | VARCHAR(20)   | Business identifier (e.g. `AST-0001`), unique   |
| asset_name      | VARCHAR(255)  | Human-readable asset name                       |
| description     | TEXT          | Detailed asset description                      |
| category_id     | INT FK        | → `asset_categories.id`                         |
| asset_type      | VARCHAR(100)  | Subtype within category (e.g. Firewall, Laptop) |
| owner_id        | INT FK        | → `users.id` — accountable asset owner          |
| department_id   | INT FK        | → `departments.id` — owning department          |
| criticality     | ENUM          | Low, Medium, High, Critical                     |
| confidentiality | INT (1–5)     | CIA rating for data sensitivity                 |
| integrity       | INT (1–5)     | CIA rating for data/system accuracy             |
| availability    | INT (1–5)     | CIA rating for uptime requirements              |
| location        | VARCHAR(255)  | Physical or logical location                    |
| status          | ENUM          | Active, Inactive, Maintenance, Retired, Pending Deployment |
| purchase_date   | DATE          | Acquisition date                                |
| warranty_expiry | DATE          | Warranty end date                               |
| created_at      | TIMESTAMP     | Record creation time                            |
| updated_at      | TIMESTAMP     | Last modification time                          |

---

## Relationships

```
departments ──┐
              ├──► assets ◄── asset_categories
users ────────┘
```

| Foreign Key                  | References              | On Delete  | Rationale                                              |
|------------------------------|-------------------------|------------|--------------------------------------------------------|
| `assets.category_id`         | `asset_categories(id)`  | RESTRICT   | Prevents deleting a category while assets depend on it |
| `assets.owner_id`            | `users(id)`             | RESTRICT   | Ensures every asset retains a valid accountable owner  |
| `assets.department_id`       | `departments(id)`       | RESTRICT   | Preserves organizational accountability on assets      |

Core schema tables (`users`, `departments`) use `ON DELETE SET NULL` for optional references. Module tables use `RESTRICT` to protect GRC data integrity — the same pattern established by the Risk Management module.

---

## Indexes

| Index                    | Column(s)     | Purpose                                    |
|--------------------------|---------------|--------------------------------------------|
| `idx_asset_status`       | status        | Filter assets by operational state         |
| `idx_asset_owner`        | owner_id      | Lookup assets by accountable owner         |
| `idx_asset_department`   | department_id | Department-scoped asset views              |
| `idx_asset_category`     | category_id   | Category-based filtering and reporting     |
| `idx_asset_criticality`  | criticality   | Priority-based triage and dashboards       |
| `idx_asset_type`         | asset_type    | Subtype search and grouping                |

The unique constraint on `asset_id` provides an implicit index for business-key lookups.

---

## Design Decisions

### Why CIA values are stored separately

Confidentiality, Integrity, and Availability are stored as three independent 1–5 ratings rather than a single combined score. This mirrors standard information security frameworks (NIST, ISO 27001) where each dimension can differ — for example, a public website may have low confidentiality but high availability. Storing them separately allows:

- Future calculation of a derived asset risk score (e.g. weighted CIA average)
- Granular filtering (e.g. "all assets with confidentiality ≥ 4")
- Alignment with Control and Audit modules that may assess individual CIA dimensions

CHECK constraints enforce the 1–5 range at the database level.

### Why criticality is stored independently

The `criticality` field (Low / Medium / High / Critical) represents a **business-level judgment** about how important an asset is to the organization. It is distinct from CIA ratings:

- **CIA ratings** describe the security properties of the asset's data and services
- **Criticality** describes the business impact if the asset is compromised or unavailable

An asset owner or risk manager may assign Critical business criticality even when CIA scores are moderate, based on regulatory exposure or revenue dependency. Keeping both allows the Risk Mapping phase to correlate asset criticality with registered risks using either business context or technical classification.

### How this schema prepares for Risk Mapping

The existing `risks` table already references `departments` and `users` for organizational context. The asset schema shares those same foreign keys, creating a natural join path:

```
assets ──► departments ◄── risks
assets ──► users (owner) ◄── risks (owner)
```

Future phases can introduce a junction table (e.g. `asset_risk_mappings`) linking `assets.id` to `risks.id` without modifying either table. Because both modules use:

- Consistent business ID formats (`AST-0001`, `RSK-0001`)
- Shared organizational entities (`departments`, `users`)
- Comparable classification fields (asset criticality ↔ risk impact/likelihood)

…the platform can support queries such as "show all Critical assets in IT Security with open High-impact risks" and feed the AI Copilot with structured asset-risk context.

---

## Seed Data Summary

| Asset ID  | Asset Name                   | Category                | Criticality | Owner        | Department   |
|-----------|------------------------------|-------------------------|-------------|--------------|--------------|
| AST-0001  | Active Directory Server      | Server                  | Critical    | ciso         | IT Security  |
| AST-0002  | Finance Database             | Database                | Critical    | admin        | Compliance   |
| AST-0003  | HR Management System         | Application             | High        | risk_manager | Executive    |
| AST-0004  | Corporate Website            | Application             | Medium      | auditor      | Executive    |
| AST-0005  | VPN Gateway                  | Network                 | Critical    | ciso         | IT Security  |
| AST-0006  | SIEM Server                  | Security Infrastructure | High        | ciso         | IT Security  |
| AST-0007  | Employee Laptop              | Endpoint                | Low         | risk_manager | Compliance   |
| AST-0008  | Enterprise Firewall          | Security Infrastructure | Critical    | ciso         | IT Security  |
| AST-0009  | Email Infrastructure         | Application             | High        | admin        | IT Security  |
| AST-0010  | Cloud Infrastructure         | Cloud Service           | Critical    | ciso         | IT Security  |
| AST-0011  | Production Web Server        | Server                  | High        | ciso         | IT Security  |
| AST-0012  | Backup Server                | Server                  | Critical    | admin        | Compliance   |
| AST-0013  | Internal API Server          | Server                  | High        | ciso         | IT Security  |
| AST-0014  | Network Core Switch          | Network                 | Critical    | ciso         | IT Security  |
| AST-0015  | Security Monitoring Platform | Security Infrastructure | Critical    | ciso         | IT Security  |

Three supplementary users (`ciso`, `risk_manager`, `auditor`) are seeded to provide owner diversity. All use password `admin123` (same hash as the default admin account).

---

## Execution

1. Ensure `schema.sql` has been executed (creates `grc_command_center` with core tables).
2. Open phpMyAdmin, select the `grc_command_center` database.
3. Import or paste the contents of `asset_inventory_schema.sql`.
4. Verify tables `asset_categories` and `assets` exist with seed data.
