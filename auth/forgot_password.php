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

$pageTitle = 'Forgot Password';
$error = '';
$requestedRole = trim($_GET['role'] ?? '');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim(filter_var($_POST['email'] ?? '', FILTER_SANITIZE_EMAIL));

    if (empty($email)) {
        $error = 'Please enter your registered email address.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } else {
        $stmt = $pdo->prepare('SELECT user_id, name, email, role FROM users WHERE email = ?');
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user) {
            $otp = str_pad((string)random_int(100000, 999999), 6, '0', STR_PAD_LEFT);
            $updateStmt = $pdo->prepare('
                UPDATE users 
                SET otp_code = ?, otp_expiry = DATE_ADD(NOW(), INTERVAL 15 MINUTE) 
                WHERE user_id = ?
            ');
            $updateStmt->execute([$otp, $user['user_id']]);

            send_otp_email($user['email'], $user['name'], $otp, 'password_reset');

            $_SESSION['reset_email'] = $user['email'];
            $_SESSION['reset_role']  = $user['role'];

            header('Location: ' . BASE_URL . 'auth/verify_otp.php?type=forgot');
            exit;
        } else {
            $error = 'No account found with this email address.';
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
                    <?php if ($requestedRole === 'admin'): ?>
                        <div class="badge bg-danger-subtle text-danger border border-danger-subtle px-3 py-2 rounded-pill mb-2 d-inline-flex align-items-center gap-1">
                            <?= icon('admin', 'app-icon') ?> Admin Password Recovery
                        </div>
                    <?php endif; ?>
                    <h3 class="fw-bold mb-1">Forgot Password?</h3>
                    <p class="text-body-secondary small">
                        Enter your registered email address and we'll send you a 6-digit OTP to reset your password.
                    </p>
                </div>

                <?php if ($error): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <?= htmlspecialchars($error) ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>

                <form method="POST" action="<?= BASE_URL ?>auth/forgot_password.php<?= $requestedRole === 'admin' ? '?role=admin' : '' ?>" novalidate>
                    <div class="mb-4">
                        <label for="email" class="form-label">Email Address</label>
                        <input type="email" class="form-control" id="email" name="email" 
                               placeholder="name@example.com" 
                               value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" 
                               required autofocus>
                    </div>

                    <button type="submit" class="btn btn-primary w-100 py-2 fw-semibold">
                        Send Reset OTP
                    </button>
                </form>

                <hr class="my-4">

                <div class="d-flex flex-column gap-2 text-center small">
                    <div>
                        <a href="<?= BASE_URL ?>auth/login.php" class="text-decoration-none">
                            &larr; Back to Student &amp; Teacher Login
                        </a>
                    </div>
                    <div>
                        <a href="<?= BASE_URL ?>auth/admin_login.php" class="text-decoration-none text-danger">
                            Admin Portal Login
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
