-- Student Feedback System - Minimal Auth Schema
CREATE DATABASE IF NOT EXISTS student_feedback;
USE student_feedback;

-- USERS TABLE
CREATE TABLE IF NOT EXISTS users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    phone VARCHAR(15) NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('student', 'teacher', 'admin') NOT NULL,
    student_number VARCHAR(30) UNIQUE NULL,
    employee_id VARCHAR(30) UNIQUE NULL,
    department VARCHAR(100) NULL,
    semester VARCHAR(20) NULL,
    section VARCHAR(20) NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    is_verified BOOLEAN DEFAULT FALSE,
    otp_code VARCHAR(10) NULL,
    otp_expiry DATETIME NULL
);
