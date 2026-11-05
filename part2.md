# Part 2: Student Feedback Submission Engine

## 1. Overview & Objectives
Part 2 implements the primary transaction of the system: allowing students to explore their enrolled semester courses, complete multi-criteria feedback surveys (ratings + comments) for assigned faculty, prevent duplicate submissions, review submission history, download feedback receipts, and manage personal profiles.

---

## 2. Component Checklist

### A. Student Course Catalog
- [ ] **`student/catalog.php`**:
  - Displays courses for the student's current department and semester by default.
  - Search bar: Filter dynamically by course name or course code.
  - Dropdown filters: Filter by Department and Semester.
  - Course card / table displays: Course Code, Course Name, Department, Semester, Section, and Assigned Instructor.
  - Action button: "Give Feedback" (disabled or shows "Submitted" badge if feedback already provided for that course assignment).

---

### B. Rating & Feedback Submission Form
- [ ] **`student/feedback_submit.php`** (Form + POST Handler):
  - **Course & Instructor Context**: Displays course title, code, and assigned faculty name at the top of the form.
  - **Dynamic Survey Questions**: Fetches questions from `questions` table and renders interactive 1–5 star rating inputs (or radio buttons) for each question.
  - **Qualitative Comments**: General comments / remarks textarea.
  - **Server-Side PHP Validation**:
    - Validates that a rating between 1 and 5 is chosen for every question.
    - Validates that the comments field is not empty.
    - **Duplicate Submission Guard**: Checks database (`feedback` table for `student_id` + `assignment_id`) in PHP to guarantee a student cannot submit duplicate feedback for the same course assignment.
  - **Transactional Storage**:
    - Inserts record into `feedback` table with default status `submitted`.
    - Inserts each answer into `feedback_answers` (`feedback_id`, `question_id`, `rating`, `answer_text`).
    - Sets success flash message and redirects to `student/my_feedback.php`.

---

### C. Student Dashboard & Feedback History
- [ ] **`student/dashboard.php`**:
  - [x] Basic stub exists (`auth_check_student.php` + layout).
  - [ ] Implement KPI summary cards: Total Feedback Submitted, Pending Reviews, Approved/Resolved Feedback.
  - [ ] Quick shortcut buttons ("Browse Course Catalog", "My Feedback History").
  - [ ] Table of recently submitted feedback entries.
- [ ] **`student/my_feedback.php`**:
  - Full tabular history of all feedback submitted by the logged-in student.
  - Columns: Course Name, Course Code, Faculty Name, Date Submitted, Status Badge (`Submitted`, `Reviewed`, `Resolved`), Actions ("View Details", "Download").
- [ ] **`student/feedback_detail.php?id=`**:
  - Detailed read-only view of a submitted feedback entry.
  - Displays all questions answered with corresponding star ratings, submission timestamp, status workflow indicator, and qualitative comments.
- [ ] **Feedback Download Receipt (`student/download_feedback.php?id=`)**:
  - Server-side generation and download of feedback receipt (formatted Text or PDF file) for the student's records.

---

### D. Student Profile Management
- [ ] **`student/profile.php`**:
  - Displays current profile: Name, Email, Student Roll/Reg Number, Department, Semester, Section, Phone.
  - Editable fields: Full Name, Phone Number.
  - Password change section: Current Password verification + New Password (min. 6 characters) + Confirm New Password.
  - Form validation with flash message confirmation upon update.

---

## 3. Progress Summary
| Category | Total Items | Already Made | To Implement |
| :--- | :---: | :---: | :---: |
| Course Catalog | 1 | 0 | 1 |
| Feedback Submission Flow | 1 | 0 | 1 |
| Dashboard & Submission History | 4 | 0.5 (stub) | 3.5 |
| Student Profile Management | 1 | 0 | 1 |
| **Total** | **7** | **0.5 (7%)** | **6.5 (93%)** |
