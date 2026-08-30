# School Admission Portal Installation

## 1. Database

Open phpMyAdmin or MySQL console and import:

`database/schema.sql`

This creates the `school_admission_portal` database, tables, indexes, foreign keys, settings, sample classes, and one admin account.

For an existing installation, also import:

`database/white_label_migration.sql`

`database/payment_flow_migration.sql`

`database/admission_letter_status_migration.sql`

## 2. Configure

Edit `config/config.php` if your MySQL details differ:

```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'school_admission_portal');
define('DB_USER', 'root');
define('DB_PASS', '');
```

You can also add Paystack and Monnify keys from Admin > Settings > Payment Gateways. Values saved there override the defaults in `config/config.php`.

Payment gateway setup:

- Set `Payment Mode` to `Test / Sandbox` while testing.
- Add Paystack keys such as `pk_test_...` and `sk_test_...`.
- Add Monnify sandbox API key, secret key, and contract code if using Monnify.
- Switch `Payment Mode` to `Live / Production` only when you add live keys.

SMTP email settings can be edited in Admin > Settings or in `config/config.php`:

- `smtp_host`
- `smtp_port`
- `smtp_secure`
- `smtp_username`
- `smtp_password`
- `smtp_from_email`
- `smtp_from_name`

Webhook URLs:

- Paystack: `http://localhost/schooladmintionporter/webhook/paystack`
- Monnify: `http://localhost/schooladmintionporter/webhook/monnify`

## 3. Login

Public portal:

`http://localhost/schooladmintionporter/`

Setup wizard:

`http://localhost/schooladmintionporter/setup/`

Admin panel:

`http://localhost/schooladmintionporter/admin/login`

Default admin:

Email: `admin@school.test`

Password: `admin123`

## 4. Customization

Use Admin > Settings to update school name, address, phone, email, principal name, and admission fee.

The theme color is deep blue and can be adjusted in `assets/css/style.css`.

## 5. Notes

Paystack, SMS, and email hooks are present. Add live Paystack keys in `config/config.php` and connect your preferred SMS provider inside `send_sms_notice()` in `config/helpers.php`.

For PDF export, the printable admission letter and browser "Save as PDF" flow is available. A library such as Dompdf can be added later for server-generated PDF files.

## 6. White Label

Use Admin > Settings to change logo, favicon, school identity, fees, dashboard counters, and theme colors. These values update the UI without source code changes.
