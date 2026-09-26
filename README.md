# Student Feedback System

A web-based **Student Feedback System** built using PHP, MySQL, JavaScript, HTML, and Bootstrap CSS.

## Tech Stack

* **HTML** — Page structure
* **Bootstrap CSS** — UI styling and responsive design (with native Dark / Light / Auto mode)
* **JavaScript** — Client-side interactions and dynamic form handling
* **PHP** — Backend / server-side logic
* **MySQL** — Relational database

---

## Requirements

Install PHP (with MySQL PDO extension) and MySQL server locally:

* **Ubuntu / Debian / WSL:**
  ```bash
  sudo apt update
  sudo apt install -y php-cli php-mysql mysql-server
  ```
* **Fedora / RHEL:**
  ```bash
  sudo dnf install -y php-cli php-mysqlnd mysql-server
  ```

Make sure the MySQL service is running:
```bash
sudo systemctl start mysql    # Ubuntu / Debian / WSL
# or
sudo systemctl start mysqld   # Fedora / RHEL
```

---

## Installation & Setup

### 1. Clone the repository

```bash
git clone https://github.com/blezecon/Student-Feedback-sys.git
cd Student-Feedback-sys
```

### 2. Configure Database Credentials (Optional)

If your local MySQL uses a password, open `config/db_connect.php` and set it:

```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'student_feedback');
define('DB_USER', 'root');
define('DB_PASS', ''); // Set your MySQL password here if not blank
```

### 3. Import Database Schema

Import the schema to create the database and required tables:

```bash
mysql -u root -p < database/schema.sql
# Or if root has no password / using sudo:
sudo mysql < database/schema.sql
```

> **WSL / Ubuntu Troubleshooting:** If you get `Access denied for user 'root'@'localhost'`, run this once in terminal:
> ```bash
> sudo mysql -e "ALTER USER 'root'@'localhost' IDENTIFIED BY ''; FLUSH PRIVILEGES;"
> ```

---

## Running the Application

Open a terminal in the project's root directory:

```bash
php -S localhost:8000
```

Open your browser and visit:

**http://localhost:8000**

*(Press `Ctrl + C` in the terminal to stop the server).*

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

To set up your initial Administrator account:

1. Open **http://localhost:8000/auth/register.php** in your browser and register an account.
2. Promote your account to **Admin** by running this one-liner in your terminal:

```bash
sudo mysql student_feedback -e "UPDATE users SET role = 'admin', is_verified = 1 WHERE email = 'your_email@gmail.com';"
```

*(Or if your MySQL root uses a password: `mysql -u root -p student_feedback -e "UPDATE users SET role = 'admin', is_verified = 1 WHERE email = 'your_email@gmail.com';"`)*

3. You can now log into the **Admin Portal** at **http://localhost:8000/auth/admin_login.php**.
4. If an admin forgets their password, they can reset it using the "Forgot password?" link on the Admin Portal login page.

---

## Notes

* Bootstrap and all SVG icons are stored locally in `assets/`, so no internet connection is required to run the frontend.
* PHPMailer is bundled locally in `includes/PHPMailer/` without requiring Composer or npm.
* MySQL must be running while using the application.
* PHP's built-in web server is intended for local development and testing.
