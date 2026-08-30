# EduCore Auto-Updater & Software Releases Documentation

This document describes the automated update mechanism and release workflow for **EduCore Commercial Software**.

---

## 1. Automated Update Workflow

```
Check License Key & Status
       ↓
Query License Server Latest Version (/api/v1/update/check)
       ↓
Download Signed Update Package ZIP
       ↓
Verify SHA256 Integrity & Digital Signature
       ↓
Create Full File Backup in /backups/
       ↓
Extract Update Files to Core Workspace
       ↓
Execute Database Schema Migrations
       ↓
Update Complete
```

---

## 2. Manual Update Instructions

If automatic updates are disabled by server policy:
1. Log in to **SkySavingTech Customer Portal**.
2. Download the target update release ZIP package.
3. Verify the downloaded package SHA256 checksum:
   ```powershell
   Get-FileHash EduCore-v1.1.0.zip -Algorithm SHA256
   ```
4. Extract files over your root EduCore directory.
5. Access `http://your-domain.com/admin` to trigger pending database schema migrations.
