# EduCore Troubleshooting & Diagnostic Guide

Troubleshooting guide for common self-hosted deployment and licensing errors.

---

## Common Issues & Solutions

### 1. "Domain Mismatch Error"
- **Symptom**: System displays a banner stating license is locked to another domain.
- **Cause**: License key was activated on domain A, but system is currently being accessed via domain B.
- **Fix**: Log in to SkySavingTech Customer Portal -> License Management -> Transfer Domain Lock to new domain.

### 2. "30-Day Offline Grace Period Expired"
- **Symptom**: EduCore admin panel is locked due to offline grace period expiry.
- **Cause**: Server has been unable to communicate with SkySavingTech License Server for 30 consecutive days.
- **Fix**: Ensure outbound cURL connections to `http://localhost/EduCore-LicenseServer` are unblocked on port 80/443, then click "Sync License Now".

### 3. "Database Migration Failed During Update"
- **Symptom**: Updater reports migration execution failure.
- **Fix**: Restore system files from the automatically created backup ZIP in `/backups/`, check database user permissions, and re-run update.

### 4. "Invalid Signature / Verification Failure"
- **Symptom**: Downloaded update package fails signature check.
- **Fix**: Download fresh package from SkySavingTech Customer Portal Download Center.
