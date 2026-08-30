# EduCore API Documentation

This document describes the REST API endpoints and state routes exposed by the EduCore platform.

---

## 1. Authentication & Security
POS client requests to the POS Device API require the following custom HTTP headers for message validation and cryptographic signature verification:

- `X-Device-Token`: The secure session token returned during device login.
- `X-School-Id`: The tenant school ID integer.
- `X-Timestamp`: Unix timestamp (requests older than 15 minutes are rejected for replay protection).
- `X-Signature`: SHA256 signature calculated as `sha256(device_token + school_id + timestamp)`.

---

## 2. API Endpoints

### 2.1 SaaS License Verification
Verify current SaaS active subscription and key associations.
* **Method**: `POST`
* **Route**: `?route=api/license/verify`
* **Request Parameters**:
  - `license_key` (string, required)
  - `api_key` (string, required)
* **Response Example (Success)**:
  ```json
  {
    "status": "success",
    "valid": true,
    "school_name": "Westfield Academy",
    "plan": "Premium",
    "domain": "westfield.edu.ng",
    "expires_at": "2027-12-31 23:59:59",
    "grace_days": 7
  }
  ```

### 2.2 OTA System Updates
Get the latest software version build and package paths.
* **Method**: `GET`
* **Route**: `?route=api/license/updates`
* **Response Example (Success)**:
  ```json
  {
    "status": "success",
    "latest_version": "2.1.0",
    "release_notes": "Added local QR generation and database indexing optimization.",
    "download_url": "https://educore.skysaveings.com.ng/uploads/updates/v2.1.0.zip",
    "sql_migration_url": "https://educore.skysaveings.com.ng/uploads/updates/v2.1.0.sql",
    "apk_url": "https://educore.skysaveings.com.ng/uploads/updates/v2.1.0.apk"
  }
  ```

### 2.3 Device Registration / Login
Activate a new POS terminal scanner device under a school's context.
* **Method**: `POST`
* **Route**: `?route=api/device/login`
* **Request Parameters**:
  - `school_code` (string, required)
  - `activation_code` (string, required)
  - `device_model` (string, optional, default "POS Terminal")
  - `android_version` (string, optional, default "9.0")
  - `serial_number` (string, optional)
  - `battery_level` (int, optional)
* **Response Example (Success)**:
  ```json
  {
    "status": "success",
    "device_token": "SKY-DEV-a964302521c7d245c369e5d800ef...",
    "school_id": 2,
    "school_name": "Westfield Academy",
    "location": "Main Gate"
  }
  ```

### 2.4 Submit Attendance Scan
Record check-in transaction matching a student's card.
* **Method**: `POST`
* **Route**: `?route=api/device/attendance`
* **Headers**: Required (see Section 1).
* **Request Parameters**:
  - `qr_code` (string, required) - Raw string or full URL containing token query parameter.
  - `scan_time` (string, optional, HH:MM format)
* **Response Example (Success)**:
  ```json
  {
    "status": "success",
    "attendance_status": "success",
    "message": "Attendance Marked.",
    "student_name": "Eyitayo Azzan",
    "class": "Primary 6",
    "admission_number": "SCH/2026/0002",
    "passport_photo": "https://westfield.edu.ng/uploads/passports/a1629df.webp",
    "time_in": "07:15 AM",
    "status_label": "Present"
  }
  ```

---

## 3. Error Codes & Rate Limiting

### 3.1 HTTP Status Codes
- `200 OK`: Request succeeded.
- `400 Bad Request`: Missing mandatory parameters.
- `401 Unauthorized`: Device token mismatch or timestamp replay drift.
- `403 Forbidden`: Signature verification failed or device blocked.
- `404 Not Found`: Student not found.
- `429 Too Many Requests`: Rate limit threshold exceeded.
- `500 Internal Server Error`: Server exception logged in `Logger`.

### 3.2 Rate Limits
To prevent brute-forcing and abuse, the system imposes the following rate limit thresholds per client IP:
- **Device Logins**: Maximum 5 attempts per 15 minutes.
- **Attendance Submissions**: Maximum 60 requests per minute.
