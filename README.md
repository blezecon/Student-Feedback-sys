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

## Creating an Admin Account

Public registration is only available for **Students** and **Teachers**. Newly registered accounts require admin approval before they can log in.

1. Open **http://localhost:8000/auth/register.php** and register an account.
2. Promote the account to **Admin**:

```bash
podman compose exec db mysql -u root -proot student_feedback -e "UPDATE users SET role = 'admin', is_verified = 1 WHERE email = 'your_email@gmail.com';"
```

3. Log into the **Admin Portal** at **http://localhost:8000/auth/admin_login.php**.

---

## Notes

* Bootstrap and all SVG icons are stored locally in `assets/`, no external CDNs required.
* Project root is volume-mounted to `/var/www/html`; file edits reflect immediately.
