# Part 3: Teacher Portal, Admin Workflow, Moderation & Analytics

## 1. Overview & Objectives
Part 3 delivers the stakeholder feedback loop: enabling teachers to analyze performance metrics and student comments for their courses, and providing administrators with full oversight through moderation workflows (`Submitted` $\rightarrow$ `Reviewed` $\rightarrow$ `Resolved`), low-rating alerts, system-wide analytics, and CSV data exports.

---

## 2. Component Checklist

### A. Teacher Portal
- [ ] **`teacher/dashboard.php`**:
  - [x] Basic stub exists (`auth_check_teacher.php` + layout).
  - [ ] Summary cards: Total Assigned Courses, Total Feedback Received, Overall Teacher Average Rating (stars).
  - [ ] List of assigned courses with average ratings per course.
  - [ ] Quick shortcut to recent student feedback comments.
- [ ] **`teacher/catalog.php`** (My Courses / Assignments Listing):
  - Lists all courses, departments, semesters, and sections assigned to the logged-in teacher.
  - Summary metrics per course: student feedback submission count, average rating score.
  - Link to view detailed feedback for each course.
- [ ] **`teacher/feedback_detail.php?id=`**:
  - Read-only detailed breakdown of student feedback for the teacher's course:
    - Submission date and workflow status.
    - Question-by-question rating breakdown and written student comments.
- [ ] **`teacher/reports.php` / Summary Download**:
  - Generate and download a consolidated summary report (CSV or printable summary) for assigned courses showing feedback aggregates.
- [ ] **`teacher/profile.php`**:
  - View Employee ID, Email, Department.
  - Editable fields: Full Name, Phone Number.
  - Change password section with current password verification.

---

### B. Admin Feedback Moderation & Status Workflow
- [ ] **`admin/feedback/list.php`** (All Feedback Listing):
  - Displays all feedback across all students, teachers, and courses in a central table.
  - Filter by status (`submitted`, `reviewed`, `resolved`), course, or teacher.
  - Quick action buttons to transition status or view details.
- [ ] **`admin/feedback/detail.php?id=`**:
  - Full view of the feedback entry: student name/number, course code/name, teacher name, submission timestamp, question ratings, and written comments.
  - Status change dropdown / button to update status.
- [ ] **`admin/feedback/update_status.php`**:
  - Status transition handler (restricted to Admin):
    - Validates transitions between `submitted` $\rightarrow$ `reviewed` $\rightarrow$ `resolved`.
    - Updates timestamp / record in `feedback` table.
    - Sets flash notification and redirects back to feedback list or detail page.
- [ ] **`admin/feedback/flagged.php`** (Flagged / Low-Rated Courses):
  - Filtered view of courses or faculty members whose average rating falls below a defined quality threshold (e.g., $< 3.0$ out of 5).
  - Highlights specific low-rated feedback comments for academic review and corrective action.

---

### C. Admin Dashboard Analytics, CSV Export & User Verification
- [ ] **`admin/dashboard.php`**:
  - [x] Basic stub exists (`auth_check_admin.php` + layout).
  - [ ] KPI Metrics: Total Feedback Submissions, Overall Average Rating, Pending Reviews Count, Flagged Courses Count.
  - [ ] Status distribution counters (`Submitted`, `Reviewed`, `Resolved`).
  - [ ] Quick links to Manage Courses, Review Flagged Courses, and Export Reports.
- [ ] **`admin/feedback/export.php`**:
  - Server-side generator that queries feedback and exports all records (or filtered records) directly into a `.csv` file download with appropriate HTTP headers (`Content-Type: text/csv`).
- [ ] **`admin/users/` / Account Verification**:
  - Listing of newly registered students and teachers currently in `is_verified = 0` status.
  - Admin approval action button to verify accounts (`UPDATE users SET is_verified = 1 WHERE user_id = ?`) so they can log in.
- [ ] **`admin/profile.php`**:
  - View admin account details, update name and contact number, and update admin password.

---

## 3. Progress Summary
| Category | Total Items | Already Made | To Implement |
| :--- | :---: | :---: | :---: |
| Teacher Portal | 5 | 0.5 (stub) | 4.5 |
| Feedback Moderation Workflow | 4 | 0 | 4 |
| Admin Dashboard Analytics & Export | 4 | 0.5 (stub) | 3.5 |
| **Total** | **13** | **1 (8%)** | **12 (92%)** |
