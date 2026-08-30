# EduCore Commercial License & Transfer Policy

EduCore is distributed under a commercial self-hosted proprietary license by **SkySavingTech**.

---

## 1. License Tiers & Feature Matrix

| Feature | Basic License | Professional License | Enterprise License |
| :--- | :--- | :--- | :--- |
| **Domain Lock** | 1 Domain | Up to 3 Domains | Unlimited Domains |
| **Updates Eligibility** | 1 Year Included | 1 Year Included | Lifetime Included |
| **Domain Transfers** | 3 Transfers Max | 5 Transfers Max | Unlimited Transfers |
| **Offline Grace Period**| 30 Days | 30 Days | 30 Days |
| **Support Level** | Standard Ticket Support | Priority Ticket Support | Dedicated 24/7 Support |

---

## 2. License Activation & Offline Grace Period

- **Activation Flow**: During web installation or initial setup, EduCore contacts `https://license.skysavingtech.com/api/v1/license/activate` with the stored license key and current server HTTP domain.
- **Offline Grace Period**: EduCore performs background license checks once every 24 hours. If the server loses internet connectivity, EduCore enters a **30-day offline grace period**. School operations continue without interruption during this period.

---

## 3. Domain Transfer System

To move your EduCore installation from an old domain (e.g. `oldschool.com`) to a new domain (e.g. `newschool.com`):
1. Log in to **SkySavingTech Customer Portal** (`http://localhost/SkySavingTech-CustomerPortal`).
2. Navigate to **License Management**.
3. Click **Transfer Domain Lock**.
4. Enter the new domain name and click **Confirm Transfer**.
5. Log in to EduCore on your new server and click **Sync License**.
