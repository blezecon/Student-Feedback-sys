# Student Feedback System

A web-based **Student Feedback System** built with PHP, MySQL, JavaScript, HTML, and Bootstrap CSS.

## Tech Stack

* **HTML & Bootstrap CSS** — UI with native Dark / Light / Auto mode
* **JavaScript** — Dynamic form handling
* **PHP 8.2** — Backend logic
* **MySQL 8.0** — Relational database
* **Podman / Docker** — Containerized environment

---

## Requirements

* [Podman](https://podman.io/) and `podman-compose` (or Docker & Docker Compose)

---

## Quick Start

### 1. Clone the repository

```bash
git clone https://github.com/blezecon/Student-Feedback-sys.git
cd Student-Feedback-sys
```

### 2. Start the containers

```bash
podman compose up -d
```

The database schema in `database/schema.sql` imports automatically on first boot.

### 3. Open in browser

Visit **http://localhost:8000**

To stop the containers:
```bash
podman compose down
```

---

## Email Configuration & PHPMailer

The system uses **PHPMailer** (`includes/PHPMailer/`) for sending 6-digit OTP codes for:
1. **Account Email Verification** (Students & Teachers upon registration).
2. **Forgot Password Reset** (Students, Teachers, and Admins).

### 1. SMTP Setup (Optional for Local Testing)
Configure your SMTP provider credentials in [config/mail.php](file:///home/blezecon/College/Student-Feedback-sys/config/mail.php):
```php
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USER', 'your_email@gmail.com');
define('SMTP_PASS', 'your_16_char_app_password'); // Gmail App Password
define('SMTP_SECURE', 'tls');
define('MAIL_FROM_EMAIL', 'your_email@gmail.com');
define('MAIL_FROM_NAME', 'Student Feedback System');
```

> **Built-in Development Mode:** If `config/mail.php` has placeholder credentials, the application automatically displays the 6-digit OTP code directly on the verification screen. This allows seamless local testing and viva demonstrations without needing live internet or SMTP access.

---

## Creating an Admin Account

Public registration is only available for **Students** and **Teachers**. Newly registered accounts verify their email via a 6-digit OTP code.

1. Open **http://localhost:8000/auth/register.php** and register an account.
2. Promote the account to **Admin**:

```bash
podman compose exec db mysql -u root -proot student_feedback -e "UPDATE users SET role = 'admin', is_verified = 1 WHERE email = 'your_email@gmail.com';"
```
*(Or if your MySQL root uses a password: `mysql -u root -p student_feedback -e "UPDATE users SET role = 'admin', is_verified = 1 WHERE email = 'your_email@gmail.com';"`)*

3. You can now log into the **Admin Portal** at **http://localhost:8000/auth/admin_login.php**.
4. If an admin forgets their password, they can reset it using the "Forgot password?" link on the Admin Portal login page.

---

## Notes

* Bootstrap and all SVG icons are stored locally in `assets/`, no external CDNs required.
* Project root is volume-mounted to `/var/www/html`; file edits reflect immediately.
