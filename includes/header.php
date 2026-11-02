<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../config/db_connect.php';
require_once __DIR__ . '/icons.php';
require_once __DIR__ . '/flash.php';

$pageTitle = isset($pageTitle) ? htmlspecialchars($pageTitle) . ' - Student Feedback' : 'Student Feedback System';
$isLoggedIn = isset($_SESSION['user_id']);
$userRole = $_SESSION['role'] ?? '';
$userName = $_SESSION['name'] ?? '';
?>
<!DOCTYPE html>
<html lang="en" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?></title>
    
    <!-- Immediate Theme Application (avoids screen flicker) -->
    <script>
        (() => {
            const stored = localStorage.getItem('sfs-theme');
            if (stored === 'dark' || (!stored && window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
                document.documentElement.setAttribute('data-bs-theme', 'dark');
            } else {
                document.documentElement.setAttribute('data-bs-theme', 'light');
            }
        })();
    </script>

    <!-- Offline Bootstrap CSS -->
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/bootstrap.min.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/style.css">
</head>
<body>
    <nav class="navbar navbar-expand-lg border-bottom shadow-sm">
        <div class="container">
            <a class="navbar-brand fw-bold d-flex align-items-center gap-2" href="<?= BASE_URL ?>index.php">
                <?= icon('logo', 'app-icon text-primary') ?>
                <span>FeedbackSys</span>
            </a>

            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarMain" aria-controls="navbarMain" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarMain">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    <li class="nav-item">
                        <a class="nav-link" href="<?= BASE_URL ?>index.php">Home</a>
                    </li>
                    <?php if ($isLoggedIn): ?>
                        <?php if ($userRole === 'student'): ?>
                            <li class="nav-item">
                                <a class="nav-link d-flex align-items-center gap-1" href="<?= BASE_URL ?>student/dashboard.php">
                                    <?= icon('student', 'app-icon') ?> Student Dashboard
                                </a>
                            </li>
                        <?php elseif ($userRole === 'teacher'): ?>
                            <li class="nav-item">
                                <a class="nav-link d-flex align-items-center gap-1" href="<?= BASE_URL ?>teacher/dashboard.php">
                                    <?= icon('teacher', 'app-icon') ?> Teacher Dashboard
                                </a>
                            </li>
                        <?php elseif ($userRole === 'admin'): ?>
                            <li class="nav-item">
                                <a class="nav-link d-flex align-items-center gap-1" href="<?= BASE_URL ?>admin/dashboard.php">
                                    <?= icon('admin', 'app-icon') ?> Admin Panel
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="<?= BASE_URL ?>admin/courses/index.php">Courses</a>
                            </li>
                        <?php endif; ?>
                    <?php endif; ?>
                </ul>

                <div class="d-flex align-items-center gap-2">
                    <!-- Theme Switcher Dropdown with SVG Icons -->
                    <div class="dropdown">
                        <button class="btn btn-outline-secondary btn-sm dropdown-toggle d-flex align-items-center gap-1" id="bd-theme" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <span class="theme-icon-active"><?= icon('circle-half', 'app-icon') ?></span>
                            <span id="theme-label" class="d-none d-sm-inline">Auto</span>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end shadow-sm" aria-labelledby="bd-theme">
                            <li>
                                <button type="button" class="dropdown-item d-flex align-items-center gap-2" data-bs-theme-value="light">
                                    <?= icon('sun', 'app-icon text-warning') ?> Light
                                </button>
                            </li>
                            <li>
                                <button type="button" class="dropdown-item d-flex align-items-center gap-2" data-bs-theme-value="dark">
                                    <?= icon('moon', 'app-icon text-info') ?> Dark
                                </button>
                            </li>
                            <li>
                                <button type="button" class="dropdown-item d-flex align-items-center gap-2 active" data-bs-theme-value="auto">
                                    <?= icon('circle-half', 'app-icon') ?> Auto (System)
                                </button>
                            </li>
                        </ul>
                    </div>

                    <!-- Auth Links -->
                    <?php if ($isLoggedIn): ?>
                        <div class="dropdown">
                            <button class="btn btn-outline-primary btn-sm dropdown-toggle d-flex align-items-center gap-2" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <?= icon($userRole, 'app-icon') ?>
                                <span class="badge bg-primary text-uppercase"><?= htmlspecialchars($userRole) ?></span>
                                <span><?= htmlspecialchars($userName) ?></span>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                                <li>
                                    <a class="dropdown-item text-danger d-flex align-items-center gap-2" href="<?= BASE_URL ?>auth/logout.php">
                                        <?= icon('logout', 'app-icon text-danger') ?> Logout
                                    </a>
                                </li>
                            </ul>
                        </div>
                    <?php else: ?>
                        <a href="<?= BASE_URL ?>auth/login.php" class="btn btn-outline-primary btn-sm">Login</a>
                        <a href="<?= BASE_URL ?>auth/register.php" class="btn btn-primary btn-sm">Register</a>
                        <a href="<?= BASE_URL ?>auth/admin_login.php" class="btn btn-outline-danger btn-sm d-flex align-items-center gap-1" title="Admin Portal">
                            <?= icon('admin', 'app-icon') ?> Admin
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>

    <main class="py-4">
        <div class="container">
        <?php if (has_flash()): $flash = get_flash(); ?>
            <div class="alert alert-<?= htmlspecialchars($flash['type']) ?> alert-dismissible fade show" role="alert">
                <?= htmlspecialchars($flash['message']) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>