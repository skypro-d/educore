# School Admission & Enrollment Management System (EduCore)

A modern white-label school admission portal for primary and secondary schools. It supports online applications, applicant tracking, admin review, payments, admission letters, parent dashboard, branding settings, and reporting.

---

## 1. System Architecture

```text
       ┌────────────────────────────────────────────────────────┐
       │                 Public Web Interface                   │
       │     (Landing Page, Application Form, Tracking View)     │
       └───────────────────────────┬────────────────────────────┘
                                   │
                                   ▼
       ┌────────────────────────────────────────────────────────┐
       │                Application Routing Layer               │
       │                  (index.php, helper.php)               │
       └───────────────────────────┬────────────────────────────┘
                                   │
                 ┌─────────────────┴─────────────────┐
                 ▼                                   ▼
       ┌───────────────────┐               ┌───────────────────┐
       │   Admin Portal    │               │   POS Device API  │
       │   (Management,    │               │   (Sync, Scan,    │
       │  Review, Reports) │               │    Telemetry)     │
       └─────────┬─────────┘               └─────────┬─────────┘
                 │                                   │
                 └─────────────────┬─────────────────┘
                                   ▼
       ┌────────────────────────────────────────────────────────┐
       │                  SaaS Scoping Wrapper                  │
       │                (TenantPDO / SchoolContext)             │
       └───────────────────────────┬────────────────────────────┘
                                   │
                                   ▼
       ┌────────────────────────────────────────────────────────┐
       │                   MySQL Database                       │
       │              (Isolated Scoped Tables)                  │
       └────────────────────────────────────────────────────────┘
```

---

## 2. Feature Checklist

- [x] Premium public admissions landing page with responsive widgets.
- [x] Online student application forms with secure document uploads.
- [x] Unique applicant ID formatting: `ADM-2026-00001`.
- [x] Live applicant tracking and status auditing.
- [x] Integrated parent dashboards.
- [x] Admin dashboard with real-time statistics, metrics, and line charts.
- [x] Multi-tenant data isolation using automatic query rewriter (`TenantPDO`).
- [x] Paystack & Monnify gateway setting management and callback hooks.
- [x] Automated SMTP transactional email with native mailer fallback.
- [x] Dynamic white-label branding (logo, crest letter, custom primary/secondary colors).
- [x] POS device registration, configuration synchronization, and battery telemetry.
- [x] Local QR code verification image generator (offline fallback).
- [x] Client request rate limiting protection.
- [x] Structured central transaction logging.

---

## 3. Installation & Run Guidelines

Refer to [DEVELOPMENT.md](DEVELOPMENT.md) for step-by-step local host installation, database migration, coding style guides, and testing procedures.

### Default Admin Login (Local Development)
* **URL**: `http://localhost/EduCore/admin/login`
* **Email**: `admin@school.test`
* **Password**: `admin123`

---

## 4. Troubleshooting & FAQ

### Q: Why does the scanner show "Student Not Found" on late check-ins?
A: When a student check-in is scanned past the school gate closing time (e.g., 9:00 AM), the engine resolves their status to `Denied`. The system displays a dedicated "Scan Denied" screen rather than matching them as present.

### Q: How do I change the maximum allowed file upload size?
A: File sizes are limited to 2MB by default. You can modify this constant in `config/config.php`:
```php
define('MAX_UPLOAD_SIZE', 2 * 1024 * 1024);
```

### Q: Where do I view log logs?
A: System events are recorded inside `logs/app.log`. The folder is automatically locked via `.htaccess` protection.

---

## 5. Extra Documentation
- [DEVELOPMENT.md](DEVELOPMENT.md) — Developer setup and local database commands.
- [SECURITY.md](SECURITY.md) — Vulnerability reporting and security controls.
- [CONTRIBUTING.md](CONTRIBUTING.md) — Pull request guidelines and code style rules.
- [CHANGELOG.md](CHANGELOG.md) — Dynamic list of version releases and fixes.
- [API_DOCUMENTATION.md](API_DOCUMENTATION.md) — Detailed POS REST endpoint schema.
