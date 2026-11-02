<?php
require_once __DIR__ . '/../../includes/auth_check_admin.php';
require_once __DIR__ . '/../../includes/Course.php';
require_once __DIR__ . '/../../includes/csrf.php';
require_once __DIR__ . '/../../includes/flash.php';

$courseModel = new Course($pdo);
$teachers    = $courseModel->teachers();
$errors      = [];

$form = [
    'course_code' => '',
    'course_name' => '',
    'department'  => '',
    'semester'    => '',
    'section'     => '',
    'teacher_id'  => '',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach (array_keys($form) as $field) {
        $form[$field] = trim((string) ($_POST[$field] ?? ''));
    }

    if (!csrf_valid()) {
        $errors[] = 'Your session expired. Please try again.';
    } else {
        $errors = $courseModel->validate($form);

        if (!$errors) {
            $courseModel->save($form);
            set_flash('success', 'Course added successfully.');
            header('Location: ' . BASE_URL . 'admin/courses/index.php');
            exit;
        }
    }
}

$pageTitle = 'Add Course';
require_once __DIR__ . '/../../includes/header.php';
?>

<div class="row justify-content-center">
    <div class="col-12 col-lg-7">
        <a href="<?= BASE_URL ?>admin/courses/index.php" class="text-decoration-none small">&larr; Back to courses</a>

        <div class="card shadow-sm border-0 my-3">
            <div class="card-body p-4">
                <h4 class="fw-bold mb-3">Add New Course</h4>

                <?php if ($errors): ?>
                    <div class="alert alert-danger">
                        <ul class="mb-0 ps-3">
                            <?php foreach ($errors as $error): ?>
                                <li><?= htmlspecialchars($error) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <form method="POST" novalidate>
                    <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">

                    <div class="row g-3">
                        <div class="col-12 col-md-6">
                            <label for="course_code" class="form-label">Course Code</label>
                            <input type="text" class="form-control" id="course_code" name="course_code"
                                   value="<?= htmlspecialchars($form['course_code']) ?>" placeholder="e.g. BCAC501" required>
                        </div>

                        <div class="col-12 col-md-6">
                            <label for="course_name" class="form-label">Course Name</label>
                            <input type="text" class="form-control" id="course_name" name="course_name"
                                   value="<?= htmlspecialchars($form['course_name']) ?>" placeholder="e.g. PHP with MySQL" required>
                        </div>

                        <div class="col-12 col-md-6">
                            <label for="department" class="form-label">Department</label>
                            <input type="text" class="form-control" id="department" name="department"
                                   value="<?= htmlspecialchars($form['department']) ?>" placeholder="e.g. BCA" required>
                        </div>

                        <div class="col-6 col-md-3">
                            <label for="semester" class="form-label">Semester</label>
                            <select class="form-select" id="semester" name="semester" required>
                                <option value="">Select</option>
                                <?php for ($i = 1; $i <= 8; $i++): $value = "Semester $i"; ?>
                                    <option value="<?= $value ?>" <?= $value === $form['semester'] ? 'selected' : '' ?>>
                                        <?= $value ?>
                                    </option>
                                <?php endfor; ?>
                            </select>
                        </div>

                        <div class="col-6 col-md-3">
                            <label for="section" class="form-label">Section</label>
                            <input type="text" class="form-control" id="section" name="section"
                                   value="<?= htmlspecialchars($form['section']) ?>" placeholder="e.g. A" required>
                        </div>

                        <div class="col-12">
                            <label for="teacher_id" class="form-label">Assign Teacher</label>
                            <select class="form-select" id="teacher_id" name="teacher_id">
                                <option value="">Unassigned</option>
                                <?php foreach ($teachers as $teacher): ?>
                                    <option value="<?= (int) $teacher['user_id'] ?>"
                                        <?= (string) $teacher['user_id'] === $form['teacher_id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($teacher['name']) ?>
                                        <?= $teacher['employee_id'] ? ' (' . htmlspecialchars($teacher['employee_id']) . ')' : '' ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <?php if (!$teachers): ?>
                                <div class="form-text">No verified teachers yet — you can assign one later.</div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary w-100 mt-4">Save Course</button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
