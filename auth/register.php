<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../config/db_connect.php';
require_once __DIR__ . '/../includes/mailer.php';
/** @var PDO $pdo */

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    $redirect = match ($_SESSION['role']) {
        'admin' => 'admin/dashboard.php',
        'teacher' => 'teacher/dashboard.php',
        default => 'student/dashboard.php',
    };
    header('Location: ' . BASE_URL . $redirect);
    exit;
}

$pageTitle = 'Register';
$errors = [];
$formData = [
    'role'           => 'student',
    'name'           => '',
    'email'          => '',
    'phone'          => '',
    'department'     => '',
    'student_number' => '',
    'semester'       => '',
    'section'        => '',
    'employee_id'    => ''
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $role           = trim($_POST['role'] ?? 'student');
    $name           = trim($_POST['name'] ?? '');
    $email          = trim(filter_var($_POST['email'] ?? '', FILTER_SANITIZE_EMAIL));
    $phone          = trim($_POST['phone'] ?? '');
    $password       = $_POST['password'] ?? '';
    $confirmPass    = $_POST['confirm_password'] ?? '';
    $department     = trim($_POST['department'] ?? '');
    $student_number = trim($_POST['student_number'] ?? '');
    $semester       = trim($_POST['semester'] ?? '');
    $section        = trim($_POST['section'] ?? '');
    $employee_id    = trim($_POST['employee_id'] ?? '');

    $formData = compact('role', 'name', 'email', 'phone', 'department', 'student_number', 'semester', 'section', 'employee_id');

    // Validation
    if (!in_array($role, ['student', 'teacher'], true)) {
        $errors[] = 'Invalid role selected.';
    }
    if ($name === '') {
        $errors[] = 'Name is required.';
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Please enter a valid email address.';
    }
    if (strlen($password) < 6) {
        $errors[] = 'Password must be at least 6 characters.';
    }
    if ($password !== $confirmPass) {
        $errors[] = 'Passwords do not match.';
    }

    if ($role === 'student') {
        if ($student_number === '') {
            $errors[] = 'Student Roll Number is required.';
        }
        if ($semester === '') {
            $errors[] = 'Semester is required.';
        }
        $employee_id = null; // Ensure null for student
    } elseif ($role === 'teacher') {
        if ($employee_id === '') {
            $errors[] = 'Employee ID is required.';
        }
        $student_number = null; // Ensure null for teacher
        $semester       = null;
        $section        = null;
    }

    // Check duplicate email / ID
    if (empty($errors)) {
        $stmt = $pdo->prepare('SELECT user_id FROM users WHERE email = ?');
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $errors[] = 'An account with this email already exists.';
        }
    }

    if (empty($errors) && $role === 'student' && $student_number) {
        $stmt = $pdo->prepare('SELECT user_id FROM users WHERE student_number = ?');
        $stmt->execute([$student_number]);
        if ($stmt->fetch()) {
            $errors[] = 'Student Roll Number is already registered.';
        }
    }

    if (empty($errors) && $role === 'teacher' && $employee_id) {
        $stmt = $pdo->prepare('SELECT user_id FROM users WHERE employee_id = ?');
        $stmt->execute([$employee_id]);
        if ($stmt->fetch()) {
            $errors[] = 'Employee ID is already registered.';
        }
    }

    // Insert user
    if (empty($errors)) {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $otp = str_pad((string)random_int(100000, 999999), 6, '0', STR_PAD_LEFT);

        $insertStmt = $pdo->prepare('
            INSERT INTO users (name, email, phone, password, role, student_number, employee_id, department, semester, section, is_verified, otp_code, otp_expiry)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 0, ?, DATE_ADD(NOW(), INTERVAL 15 MINUTE))
        ');
        $success = $insertStmt->execute([
            $name,
            $email,
            $phone ?: null,
            $hashedPassword,
            $role,
            $student_number ?: null,
            $employee_id ?: null,
            $department ?: null,
            $semester ?: null,
            $section ?: null,
            $otp
        ]);

        if ($success) {
            send_otp_email($email, $name, $otp, 'verification');
            $_SESSION['verify_email'] = $email;
            header('Location: ' . BASE_URL . 'auth/verify_otp.php?type=register');
            exit;
        } else {
            $errors[] = 'Registration failed. Please try again.';
        }
    }
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="row justify-content-center">
    <div class="col-12 col-md-8 col-lg-6">
        <div class="card shadow-sm border-0 my-4">
            <div class="card-body p-4 p-md-5">
                <h3 class="card-title text-center fw-bold mb-2">Create an Account</h3>
                <p class="text-center text-body-secondary small mb-4">Register as a Student or Teacher</p>

                <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <ul class="mb-0 ps-3">
                            <?php foreach ($errors as $error): ?>
                                <li><?= htmlspecialchars($error) ?></li>
                            <?php endforeach; ?>
                        </ul>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>

                <form method="POST" action="<?= BASE_URL ?>auth/register.php" novalidate>
                    <!-- Role Selection -->
                    <div class="mb-4">
                        <label class="form-label fw-semibold d-block">I am a:</label>
                        <div class="btn-group w-100" role="group">
                            <input type="radio" class="btn-check" name="role" id="role-student" value="student" <?= $formData['role'] === 'student' ? 'checked' : '' ?> autocomplete="off">
                            <label class="btn btn-outline-primary d-flex align-items-center justify-content-center gap-2" for="role-student">
                                <?= icon('student', 'app-icon') ?> Student
                            </label>

                            <input type="radio" class="btn-check" name="role" id="role-teacher" value="teacher" <?= $formData['role'] === 'teacher' ? 'checked' : '' ?> autocomplete="off">
                            <label class="btn btn-outline-primary d-flex align-items-center justify-content-center gap-2" for="role-teacher">
                                <?= icon('teacher', 'app-icon') ?> Teacher
                            </label>
                        </div>
                    </div>

                    <!-- Common Details -->
                    <div class="mb-3">
                        <label for="name" class="form-label">Full Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="name" name="name" value="<?= htmlspecialchars($formData['name']) ?>" required>
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-md-6">
                            <label for="email" class="form-label">Email Address <span class="text-danger">*</span></label>
                            <input type="email" class="form-control" id="email" name="email" value="<?= htmlspecialchars($formData['email']) ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label for="phone" class="form-label">Phone Number</label>
                            <input type="tel" class="form-control" id="phone" name="phone" value="<?= htmlspecialchars($formData['phone']) ?>" placeholder="Optional">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="department" class="form-label">Department</label>
                        <input type="text" class="form-control" id="department" name="department" value="<?= htmlspecialchars($formData['department']) ?>" placeholder="e.g. Computer Science">
                    </div>

                    <!-- Student-Specific Fields (Hidden when teacher is chosen) -->
                    <div id="student-fields" class="<?= $formData['role'] === 'teacher' ? 'd-none' : '' ?>">
                        <div class="mb-3">
                            <label for="student_number" class="form-label">Student Roll / Reg Number <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="student_number" name="student_number" value="<?= htmlspecialchars($formData['student_number']) ?>" data-required="true" placeholder="e.g. 2024CS042">
                        </div>

                        <div class="row g-2 mb-3">
                            <div class="col-md-6">
                                <label for="semester" class="form-label">Semester <span class="text-danger">*</span></label>
                                <select class="form-select" id="semester" name="semester" data-required="true">
                                    <option value="">Select Semester</option>
                                    <?php for ($i = 1; $i <= 8; $i++): ?>
                                        <option value="Semester <?= $i ?>" <?= $formData['semester'] === "Semester $i" ? 'selected' : '' ?>>
                                            Semester <?= $i ?>
                                        </option>
                                    <?php endfor; ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="section" class="form-label">Section</label>
                                <input type="text" class="form-control" id="section" name="section" value="<?= htmlspecialchars($formData['section']) ?>" placeholder="e.g. A">
                            </div>
                        </div>
                    </div>

                    <!-- Teacher-Specific Fields (Hidden when student is chosen) -->
                    <div id="teacher-fields" class="<?= $formData['role'] === 'student' ? 'd-none' : '' ?>">
                        <div class="mb-3">
                            <label for="employee_id" class="form-label">Employee / Staff ID <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="employee_id" name="employee_id" value="<?= htmlspecialchars($formData['employee_id']) ?>" data-required="true" placeholder="e.g. EMP1024" <?= $formData['role'] === 'student' ? 'disabled' : '' ?>>
                        </div>
                    </div>

                    <!-- Password Fields -->
                    <div class="row g-2 mb-4">
                        <div class="col-md-6">
                            <label for="password" class="form-label">Password <span class="text-danger">*</span></label>
                            <input type="password" class="form-control" id="password" name="password" required minlength="6">
                        </div>
                        <div class="col-md-6">
                            <label for="confirm_password" class="form-label">Confirm Password <span class="text-danger">*</span></label>
                            <input type="password" class="form-control" id="confirm_password" name="confirm_password" required minlength="6">
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary w-100 py-2 fw-semibold">
                        Register Account
                    </button>
                </form>

                <div class="text-center mt-4">
                    <span class="text-body-secondary">Already have an account?</span>
                    <a href="<?= BASE_URL ?>auth/login.php" class="text-decoration-none fw-semibold">Log in here</a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
