<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../config/db_connect.php';
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

$pageTitle = 'Login';
$error = '';
$info = '';
$isHtmlError = false;

if (isset($_GET['verified'])) {
    $info = 'Account verified successfully! You can now log in.';
} elseif (isset($_GET['reset'])) {
    $info = 'Password reset successfully! Please log in with your new password.';
} elseif (isset($_GET['registered'])) {
    $info = 'Registration successful! You can now log in.';
} elseif (isset($_GET['logout'])) {
    $info = 'You have been logged out successfully.';
} elseif (isset($_GET['error']) && $_GET['error'] === 'login_required') {
    $error = 'Please log in to access that page.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim(filter_var($_POST['email'] ?? '', FILTER_SANITIZE_EMAIL));
    $password = $_POST['password'] ?? '';

    if ($email === '' || $password === '') {
        $error = 'Please enter both email and password.';
    } else {
        $stmt = $pdo->prepare('SELECT user_id, name, email, password, role, is_verified FROM users WHERE email = ?');
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user) {
            if (!$user['is_verified']) {
                $_SESSION['verify_email'] = $user['email'];
                $isHtmlError = true;
                $error = 'Your account is not verified yet. <a href="' . BASE_URL . 'auth/verify_otp.php?type=register" class="alert-link">Click here to enter OTP and verify</a>.';
            } elseif (password_verify($password, $user['password'])) {
                session_regenerate_id(true);

                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['name']    = $user['name'];
                $_SESSION['email']   = $user['email'];
                $_SESSION['role']    = $user['role'];

                $target = match ($user['role']) {
                    'admin' => 'admin/dashboard.php',
                    'teacher' => 'teacher/dashboard.php',
                    default => 'student/dashboard.php',
                };
                header('Location: ' . BASE_URL . $target);
                exit;
            } else {
                // wrong password
                $error = 'Invalid password.';
            }
        } else {
            // mail not exist in db
            $error = 'Invalid email or password.';
        }
    }
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="row justify-content-center">
    <div class="col-12 col-md-6 col-lg-5">
        <div class="card shadow-sm border-0 my-4">
            <div class="card-body p-4 p-md-5">
                <div class="text-center mb-4">
                    <h3 class="fw-bold mb-1">Welcome Back</h3>
                    <p class="text-body-secondary small">Log in to Student Feedback System</p>
                </div>

                <?php if ($info): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <?= htmlspecialchars($info) ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>

                <?php if ($error): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <?= $isHtmlError ? $error : htmlspecialchars($error) ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>

                <form method="POST" action="<?= BASE_URL ?>auth/login.php" novalidate>
                    <div class="mb-3">
                        <label for="email" class="form-label">Email Address</label>
                        <input type="email" class="form-control" id="email" name="email" required autofocus value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
                    </div>

                    <div class="mb-4">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <label for="password" class="form-label mb-0">Password</label>
                            <a href="<?= BASE_URL ?>auth/forgot_password.php" class="small text-decoration-none">Forgot password?</a>
                        </div>
                        <input type="password" class="form-control" id="password" name="password" required>
                    </div>

                    <button type="submit" class="btn btn-primary w-100 py-2 fw-semibold">
                        Log In
                    </button>
                </form>

                <hr class="my-4">

                <div class="d-flex flex-column gap-2 text-center small">
                    <div>
                        <span class="text-body-secondary">Don't have an account?</span>
                        <a href="<?= BASE_URL ?>auth/register.php" class="text-decoration-none fw-semibold">Register here</a>
                    </div>
                    <div>
                        <span class="text-body-secondary">Administrator?</span>
                        <a href="<?= BASE_URL ?>auth/admin_login.php" class="text-decoration-none text-danger fw-semibold">Admin Portal</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
