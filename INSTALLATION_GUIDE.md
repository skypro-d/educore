# EduCore Commercial Software — Installation Guide

This document provides complete instructions for installing and deploying the **EduCore Standalone School Management System** on self-hosted environments.

---

## 1. System Requirements

- **PHP**: 8.0 or higher
- **Extensions**: `pdo_mysql`, `curl`, `openssl`, `mbstring`, `zip`, `json`
- **Database**: MySQL 5.7+ or MariaDB 10.3+
- **Web Server**: Apache (with `mod_rewrite` enabled) or Nginx
- **HTTPS**: Recommended SSL certificate for domain operation

---

## 2. Step-by-Step Installation

### Step 1: Upload Source Code
1. Download the EduCore distribution package (`EduCore-vX.Y.Z.zip`) from your **SkySavingTech Customer Portal**.
2. Extract the package into your web server's document root directory (e.g., `/var/www/html` or `c:/wamp64/www/EduCore`).

### Step 2: Database Preparation
1. Access your MySQL database console or phpMyAdmin.
2. Create a new empty database for EduCore:
   ```sql
   CREATE DATABASE `educore_school_db` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   ```
3. Create a dedicated database user and grant full permissions.

### Step 3: Web-Based Setup Installer
1. Open your browser and navigate to `http://your-school-domain.com/install/index.php`.
2. Step 1: System requirements & directory permissions check.
3. Step 2: Database credentials entry (`DB_HOST`, `DB_NAME`, `DB_USER`, `DB_PASS`).
4. Step 3: **License Key Verification**: Enter your 16-character commercial license key (`XXXX-XXXX-XXXX`). The installer will validate and register your domain online with SkySavingTech License Server.
5. Step 4: Administrator Account creation.
6. Step 5: Installation complete. Remove or lock the `install/` directory.

---

## 3. Post-Installation Checklist

- Verify login at `http://your-school-domain.com/admin` using your primary administrator credentials.
- Ensure cron jobs are active for daily license syncs and automated backups:
  ```bash
  0 0 * * * php /path/to/EduCore/cron/subscription_lifecycle.php >/dev/null 2>&1
  ```
