<?php
require_once __DIR__ . '/../../includes/auth_check_admin.php';
require_once __DIR__ . '/../../includes/Course.php';
require_once __DIR__ . '/../../includes/csrf.php';
require_once __DIR__ . '/../../includes/flash.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !csrf_valid()) {
    set_flash('danger', 'Invalid delete request.');
    header('Location: ' . BASE_URL . 'admin/courses/index.php');
    exit;
}

$courseModel = new Course($pdo);
$id          = (int) ($_POST['id'] ?? 0);

if ($courseModel->find($id)) {
    $courseModel->delete($id);
    set_flash('success', 'Course deleted successfully.');
} else {
    set_flash('danger', 'Course not found.');
}

header('Location: ' . BASE_URL . 'admin/courses/index.php');
exit;
