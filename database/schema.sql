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

-- COURSES TABLE
CREATE TABLE IF NOT EXISTS courses (
    course_id INT AUTO_INCREMENT PRIMARY KEY,
    course_code VARCHAR(20) NOT NULL,
    course_name VARCHAR(150) NOT NULL,
    department VARCHAR(100) NOT NULL,
    semester VARCHAR(20) NOT NULL,
    section VARCHAR(20) NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uniq_course (course_code, semester, section)
);

-- COURSE ASSIGNMENTS (course <-> teacher)
CREATE TABLE IF NOT EXISTS course_assignments (
    assignment_id INT AUTO_INCREMENT PRIMARY KEY,
    course_id INT NOT NULL,
    teacher_id INT NOT NULL,
    FOREIGN KEY (course_id) REFERENCES courses(course_id) ON DELETE CASCADE,
    FOREIGN KEY (teacher_id) REFERENCES users(user_id) ON DELETE CASCADE,
    UNIQUE KEY uniq_assignment (course_id, teacher_id)
);

-- STANDARD EVALUATION QUESTIONS
CREATE TABLE IF NOT EXISTS questions (
    question_id INT AUTO_INCREMENT PRIMARY KEY,
    question VARCHAR(255) NOT NULL UNIQUE
);

-- FEEDBACK (one per student per course assignment)
CREATE TABLE IF NOT EXISTS feedback (
    feedback_id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    assignment_id INT NOT NULL,
    status ENUM('submitted', 'reviewed', 'resolved') NOT NULL DEFAULT 'submitted',
    submitted_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (assignment_id) REFERENCES course_assignments(assignment_id) ON DELETE CASCADE,
    UNIQUE KEY uniq_feedback (student_id, assignment_id)
);

-- FEEDBACK ANSWERS (rating + comment per question)
CREATE TABLE IF NOT EXISTS feedback_answers (
    answer_id INT AUTO_INCREMENT PRIMARY KEY,
    feedback_id INT NOT NULL,
    question_id INT NOT NULL,
    rating TINYINT NOT NULL CHECK (rating BETWEEN 1 AND 5),
    answer_text TEXT NULL,
    FOREIGN KEY (feedback_id) REFERENCES feedback(feedback_id) ON DELETE CASCADE,
    FOREIGN KEY (question_id) REFERENCES questions(question_id) ON DELETE CASCADE,
    UNIQUE KEY uniq_answer (feedback_id, question_id)
);

-- SEED: default survey questions
INSERT IGNORE INTO questions (question) VALUES
    ('The course content was well-organized and easy to follow.'),
    ('The instructor explained concepts clearly.'),
    ('The instructor was available for help and answered questions.'),
    ('The course materials (notes, slides, references) were useful.'),
    ('The workload was appropriate for the credit hours.'),
    ('Overall, I am satisfied with the teaching and learning experience.');

-- SEED: sample courses for local testing
INSERT IGNORE INTO courses (course_code, course_name, department, semester, section) VALUES
    ('BCAC501', 'PHP with MySQL', 'BCA', 'Semester 5', 'A'),
    ('BCAC502', 'Web Technology', 'BCA', 'Semester 5', 'A'),
    ('BCAC503', 'Software Engineering', 'BCA', 'Semester 5', 'A'),
    ('BCAC401', 'Data Structures', 'BCA', 'Semester 4', 'A'),
    ('BBA501', 'Business Communication', 'BBA', 'Semester 5', 'A');
