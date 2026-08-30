# Security Policy (SECURITY.md)

This document describes the security controls, requirements, and vulnerability reporting procedures for the EduCore platform.

---

## 1. Vulnerability Reporting
If you identify a security vulnerability in this project, please do **NOT** open a public issue. Instead, report it privately:

- **Email**: security@westfield.edu.ng
- **PGP Key**: Available upon request.
- **Response SLA**: 48 hours for initial assessment and classification.

---

## 2. Platform Security Baselines

### 2.1 Encryption (HTTPS)
All production installations of EduCore **MUST** run behind Transport Layer Security (HTTPS). Standard HTTP connections are blocked by security headers (`Strict-Transport-Security`).

### 2.2 Security Headers
The following headers are automatically attached by the routing layer (`config/config.php`) to prevent common web attacks:
- `X-Content-Type-Options: nosniff` (MIME sniffing prevention)
- `X-Frame-Options: DENY` (Clickjacking protection)
- `X-XSS-Protection: 1; mode=block` (Cross-Site Scripting filters)
- `Content-Security-Policy` (Restricts execution to verified origins)

### 2.3 Password Requirements
Password storage uses industry-standard hashing:
- Hashing Algorithm: `PASSWORD_BCRYPT` (using cost factor 10+).
- Inactive users are automatically prompted to change default temporary credentials on their first login.

---

## 3. Data Protection Controls

### 3.1 Multi-Tenant Isolation
EduCore scopes tenant queries automatically through the `TenantPDO` wrapper using the `SchoolContext` active ID resolver. This guarantees that visitors or logged-in users cannot access tables of a different tenant school.

### 3.2 Cross-Site Request Forgery (CSRF)
All browser `POST` forms must include a CSRF token:
- Render input field: `<?= csrf_field() ?>`
- Controller verification: `verify_csrf()` is automatically run at POST routers.

### 3.3 Centralized Audit Logs
The application records critical security events inside `logs/app.log`:
- Failed and successful login attempts (with client IP hashes).
- Critical admin actions and metadata changes.
- Completed and failed payment transactions.
- System errors and exceptions.
