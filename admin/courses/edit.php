<?php
require_once __DIR__ . '/../../includes/auth_check_admin.php';
require_once __DIR__ . '/../../includes/Course.php';
require_once __DIR__ . '/../../includes/csrf.php';
require_once __DIR__ . '/../../includes/flash.php';

$courseModel = new Course($pdo);
$id          = (int) ($_GET['id'] ?? $_POST['id'] ?? 0);
$course      = $courseModel->find($id);

if (!$course) {
    set_flash('danger', 'Course not found.');
    header('Location: ' . BASE_URL . 'admin/courses/index.php');
    exit;
}

$teachers = $courseModel->teachers();
$errors   = [];

$form = [
    'course_code' => $course['course_code'],
    'course_name' => $course['course_name'],
    'department'  => $course['department'],
    'semester'    => $course['semester'],
    'section'     => $course['section'],
    'teacher_id'  => (string) ($course['teacher_id'] ?? ''),
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach (array_keys($form) as $field) {
        $form[$field] = trim((string) ($_POST[$field] ?? ''));
    }

    if (!csrf_valid()) {
        $errors[] = 'Your session expired. Please try again.';
    } else {
        $errors = $courseModel->validate($form, $id);

        if (!$errors) {
            $courseModel->save($form, $id);
            set_flash('success', 'Course updated successfully.');
            header('Location: ' . BASE_URL . 'admin/courses/index.php');
            exit;
        }
    }
}

// Semester list, plus the current value in case it is outside the standard range.
$semesters = array_map(fn ($i) => "Semester $i", range(1, 8));
if (!in_array($form['semester'], $semesters, true)) {
    $semesters[] = $form['semester'];
}

$pageTitle = 'Edit Course';
require_once __DIR__ . '/../../includes/header.php';
?>

<div class="row justify-content-center">
    <div class="col-12 col-lg-7">
        <a href="<?= BASE_URL ?>admin/courses/index.php" class="text-decoration-none small">&larr; Back to courses</a>

        <div class="card shadow-sm border-0 my-3">
            <div class="card-body p-4">
                <h4 class="fw-bold mb-3">Edit Course</h4>

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
                    <input type="hidden" name="id" value="<?= (int) $course['course_id'] ?>">

                    <div class="row g-3">
                        <div class="col-12 col-md-6">
                            <label for="course_code" class="form-label">Course Code</label>
                            <input type="text" class="form-control" id="course_code" name="course_code"
                                   value="<?= htmlspecialchars($form['course_code']) ?>" required>
                        </div>

                        <div class="col-12 col-md-6">
                            <label for="course_name" class="form-label">Course Name</label>
                            <input type="text" class="form-control" id="course_name" name="course_name"
                                   value="<?= htmlspecialchars($form['course_name']) ?>" required>
                        </div>

                        <div class="col-12 col-md-6">
                            <label for="department" class="form-label">Department</label>
                            <input type="text" class="form-control" id="department" name="department"
                                   value="<?= htmlspecialchars($form['department']) ?>" required>
                        </div>

                        <div class="col-6 col-md-3">
                            <label for="semester" class="form-label">Semester</label>
                            <select class="form-select" id="semester" name="semester" required>
                                <option value="">Select</option>
                                <?php foreach ($semesters as $value): ?>
                                    <option value="<?= htmlspecialchars($value) ?>" <?= $value === $form['semester'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($value) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-6 col-md-3">
                            <label for="section" class="form-label">Section</label>
                            <input type="text" class="form-control" id="section" name="section"
                                   value="<?= htmlspecialchars($form['section']) ?>" required>
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
                        </div>
                    </div>

                    <div class="d-flex gap-2 mt-4">
                        <button type="submit" class="btn btn-primary flex-grow-1">Update Course</button>
                        <a href="<?= BASE_URL ?>admin/courses/index.php" class="btn btn-outline-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
