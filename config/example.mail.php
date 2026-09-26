<?php
// Create a new file config/mail.php
// Then copy this whole file paste there, and put actual info
// Configure with your real email provider (e.g. Gmail App Password, Mailtrap, Brevo)
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 465);
define('SMTP_USER', 'your_email@gmail.com');
define('SMTP_PASS', 'your_gmail_app_password'); // 16-character App Password for Gmail
define('SMTP_SECURE', 'ssl');                    // 'tls' for port 587, 'ssl' for port 465
define('MAIL_FROM_EMAIL', 'your_email@gmail.com');
define('MAIL_FROM_NAME', 'Student Feedback System');
