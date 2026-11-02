<?php
require_once __DIR__ . '/../../includes/auth_check_admin.php';
require_once __DIR__ . '/../../includes/Course.php';
require_once __DIR__ . '/../../includes/csrf.php';
require_once __DIR__ . '/../../includes/flash.php';


$courseModel = new Course($pdo);

$search     = trim($_GET['search'] ?? '');
$department = trim($_GET['department'] ?? '');
$semester   = trim($_GET['semester'] ?? '');

$courses     = $courseModel->all($search, $department, $semester);
$departments = $courseModel->departments();

$pageTitle = 'Manage Courses';
require_once __DIR__ . '/../../includes/header.php';
?>

<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
    <h3 class="fw-bold mb-0">Manage Courses</h3>
    <a href="<?= BASE_URL ?>admin/courses/add.php" class="btn btn-primary">Add New Course</a>
</div>

<form method="GET" class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <div class="row g-2 align-items-end">
            <div class="col-12 col-md-4">
                <label for="search" class="form-label small">Search</label>
                <input type="text" class="form-control" id="search" name="search"
                       placeholder="Course name or code" value="<?= htmlspecialchars($search) ?>">
            </div>

            <div class="col-6 col-md-3">
                <label for="department" class="form-label small">Department</label>
                <select class="form-select" id="department" name="department">
                    <option value="">All departments</option>
                    <?php foreach ($departments as $dept): ?>
                        <option value="<?= htmlspecialchars($dept) ?>" <?= $dept === $department ? 'selected' : '' ?>>
                            <?= htmlspecialchars($dept) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-6 col-md-3">
                <label for="semester" class="form-label small">Semester</label>
                <select class="form-select" id="semester" name="semester">
                    <option value="">All semesters</option>
                    <?php for ($i = 1; $i <= 8; $i++): $value = "Semester $i"; ?>
                        <option value="<?= $value ?>" <?= $value === $semester ? 'selected' : '' ?>>
                            <?= $value ?>
                        </option>
                    <?php endfor; ?>
                </select>
            </div>

            <div class="col-12 col-md-2 d-flex gap-2">
                <button type="submit" class="btn btn-outline-primary w-100">Filter</button>
                <a href="<?= BASE_URL ?>admin/courses/index.php" class="btn btn-outline-secondary">Reset</a>
            </div>
        </div>
    </div>
</form>

<div class="card border-0 shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th>Code</th>
                    <th>Course</th>
                    <th>Department</th>
                    <th>Semester</th>
                    <th>Section</th>
                    <th>Teacher</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!$courses): ?>
                    <tr>
                        <td colspan="7" class="text-center text-body-secondary py-4">No courses found.</td>
                    </tr>
                <?php endif; ?>

                <?php foreach ($courses as $course): ?>
                    <tr>
                        <td class="fw-semibold"><?= htmlspecialchars($course['course_code']) ?></td>
                        <td><?= htmlspecialchars($course['course_name']) ?></td>
                        <td><?= htmlspecialchars($course['department']) ?></td>
                        <td><?= htmlspecialchars($course['semester']) ?></td>
                        <td><?= htmlspecialchars($course['section']) ?></td>
                        <td>
                            <?php if ($course['teacher_name']): ?>
                                <?= htmlspecialchars($course['teacher_name']) ?>
                            <?php else: ?>
                                <span class="text-body-secondary">Unassigned</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-end text-nowrap">
                            <a href="<?= BASE_URL ?>admin/courses/edit.php?id=<?= (int) $course['course_id'] ?>"
                               class="btn btn-sm btn-outline-primary">Edit</a>

                            <form method="POST" action="<?= BASE_URL ?>admin/courses/delete.php" class="d-inline"
                                  onsubmit="return confirm('Delete this course? Its assignments and feedback will also be removed.');">
                                <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                                <input type="hidden" name="id" value="<?= (int) $course['course_id'] ?>">
                                <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
