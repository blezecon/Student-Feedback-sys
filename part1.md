# Part 1: Core Foundation & Course Catalog Management

## 1. Overview & Objectives
Part 1 establishes the relational database structure, core utility helpers (flash messages, file storage), and the administrative management of courses and faculty assignments. Completing Part 1 provides the data foundation necessary for students to browse courses and submit feedback.

---

## 2. Component Checklist

### A. Database Layer (`database/schema.sql`)
- [x] **`users` table**: Created with authentication fields, role enum (`student`, `teacher`, `admin`), student/teacher identifiers, and `is_verified` flag.
- [x] **`courses` table**: Store course definitions (`course_id`, `course_code`, `course_name`, `department`, `semester`, `section`).
- [x] **`course_assignments` table**: Map courses to teachers (`assignment_id`, `course_id`, `teacher_id`, FKs with `ON DELETE CASCADE`, unique on `(course_id, teacher_id)`).
- [x] **`questions` table**: Standard evaluation questions (`question_id`, `question`).
- [x] **`feedback` table**: Root feedback submissions (`feedback_id`, `student_id`, `assignment_id`, `status` enum: `submitted`, `reviewed`, `resolved`, `submitted_at`, unique on `(student_id, assignment_id)`).
- [x] **`feedback_answers` table**: Individual ratings and answers per question (`answer_id`, `feedback_id`, `question_id`, `rating` 1-5, `answer_text`, unique on `(feedback_id, question_id)`).
- [x] **Initial Seed Data**: Default survey questions and sample courses/departments for initial testing.

---

### B. Shared Infrastructure & Helpers
- [x] **`config/db_connect.php`**: PDO MySQL connection with environment variable overrides and error handling.
- [x] **`includes/auth_check_admin.php`**: Session guard redirecting unauthorized users away from admin pages.
- [x] **`includes/auth_check_student.php`**: Session guard for student pages.
- [x] **`includes/auth_check_teacher.php`**: Session guard for teacher pages.
- [x] **`includes/header.php` & `footer.php`**: Responsive Bootstrap layout, theme switcher, and navigation bar.
- [x] **`includes/icons.php` & local SVG icons**: Theme-aware SVG icons.
- [x] **`includes/flash.php`**: Helper functions (`set_flash($type, $message)`, `get_flash()`, `has_flash()`) to display session flash alerts after redirects (e.g. "Course added successfully", "Feedback submitted").

---

### C. Admin Course Management (CRUD)
- [x] **`admin/courses/index.php`**: Course listing table:
  - Lists course code, course name, department, semester, section, and assigned teacher.
  - Search by course name or course code.
  - Filter by department and semester.
  - Action buttons: Add New Course, Edit, Delete.
- [x] **`admin/courses/add.php`**: Create Course form:
  - Inputs: `course_code`, `course_name`, `department`, `semester`, `section`.
  - Dropdown to select and assign a verified teacher (`users` where `role = 'teacher'`).
  - Server-side validation (unique course code per semester/section, required fields).
- [x] **`admin/courses/edit.php`**: Update Course form (`?id=`):
  - Pre-fills current course details and assigned teacher.
  - Allows updating course metadata and re-assigning faculty.
- [x] **`admin/courses/delete.php`**: Delete Course action (`?id=`):
  - Validates request (POST or confirmed GET with CSRF protection).
  - Deletes course and cascades associated assignments.

---

## 3. Progress Summary
| Category | Total Items | Already Made | To Implement |
| :--- | :---: | :---: | :---: |
| Database Schema | 7 | 7 | 0 |
| Shared Utilities | 7 | 7 | 0 |
| Admin Course CRUD | 4 | 4 | 0 |
| **Total** | **18** | **18 (100%)** | **0** |
