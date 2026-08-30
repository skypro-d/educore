# Development Guide (DEVELOPMENT.md)

This guide is for developers working on the EduCore multi-tenant school management system.

---

## 1. Environment Setup

### Prerequisites
- PHP >= 8.0 (with `gd`, `pdo_mysql`, `mbstring`, and `openssl` extensions enabled)
- MySQL >= 5.7 or MariaDB >= 10.2
- A local web server (WampServer, XAMPP, or Nginx)

### Steps
1. Clone this repository to your local web root directory.
2. Copy `.env.example` to `.env`:
   ```bash
   cp .env.example .env
   ```
3. Configure the parameters in `.env` (like `DB_HOST`, `DB_NAME`, `DB_USER`, `DB_PASS`, and local `APP_BASE_URL`).

---

## 2. Database Setup
1. Create a clean MySQL database matching the `DB_NAME` value in `.env`:
   ```sql
   CREATE DATABASE school_admission_portal CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   ```
2. Import the initial database schema and seed data from `database/educore_master_schema.sql`:
   ```bash
   mysql -u root -p school_admission_portal < database/educore_master_schema.sql
   ```

---

## 3. Running Locally
- **PHP Built-in Server**:
  ```bash
  php -S localhost:8000
  ```
  Then open `http://localhost:8000/` in your browser.
- **WampServer/Apache**: Configure a virtual host mapping to the root directory, or access via `http://localhost/EduCore/`.

---

## 4. Code Standards & Architecture

### Coding Conventions
- Strictly use `declare(strict_types=1);` in all PHP files.
- Follow PSR-12 coding standard conventions.
- Sanitize all browser inputs using `Validator::sanitizeString()` before executing lookups.
- Centralize raw database queries using model wrappers (like `models/Payment.php`, `models/Applicant.php`).

### Query Scoping (Multi-Tenancy)
- All tenant database queries must go through the standard PDO wrapper `TenantPDO` (configured in `config/database.php`).
- `TenantPDO` automatically intercepts queries and filters them by `school_id = SchoolContext::id()`.
- To execute a cross-tenant query (e.g. public scanning lookup), explicitly mention `school_id` inside the SQL query statement to bypass `TenantPDO` auto-scoping.

---

## 5. Deployment Guide
1. Ensure the production domain virtual host points to the root directory.
2. Copy `.env` to the production environment and configure production database and API credentials.
3. Verify that the `.env` file and `logs/` directory are **not** publicly readable (Apache `.htaccess` is configured by default).
