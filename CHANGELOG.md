# Changelog (CHANGELOG.md)

All notable changes to the EduCore project are documented here. This project adheres to Semantic Versioning.

---

## [2.1.0] - 2026-07-04

### Added
- **Centralized Email Class (`config/Email.php`)**: Decoupled SMTP/PHPMailer code from `NotificationController` with automatic native `mail()` fallback.
- **Centralized Logger Class (`config/Logger.php`)**: Writes errors, exceptions, login attempts, payments, and admin actions to `logs/app.log` (with `.htaccess` security protection).
- **centralized Validator Class (`config/Validator.php`)**: Provides static validators for email, phone, string, files, and standard data types.
- **Centralized RateLimiter Class (`config/RateLimiter.php`)**: Restricts brute-force and spam attempts dynamically based on IP hashes.
- **Environment Template (`.env.example`)**: Added environment template defining database, SMTP, Paystack, and debug settings.
- **Gitignore Rule Template (`.gitignore`)**: Added file matching rules to exclude uploads, vendor, and environment configs.
- **Database Index Optimization**: Safely registered performance indexes on tables `applicants`, `payments`, `admission_letters`, and `email_logs`.
- **Security documentation files (`SECURITY.md`, `DEVELOPMENT.md`, `CONTRIBUTING.md`, `CHANGELOG.md`)**.

### Changed
- **Config.php Refactoring**: Removed all hardcoded SMTP passwords and test Paystack keys. Now fully resolves credentials from environment variables with fallback default configurations for local testing.
- **Global Error Handling**: Added custom global exception and error handlers to log problems securely in production.
- **Security Headers**: Integrated standard headers (nosniff, clickjacking deny, CSP, HSTS) to enhance application resistance to XSS/injections.
- **Session Timeout**: Implemented an automated session timeout (1 hour default) checking logic.
- **NotificationController Refactoring**: Converted standard mail methods to delegate to the new `Email` class.
