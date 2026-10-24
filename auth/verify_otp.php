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

$type = trim($_GET['type'] ?? ($_POST['type'] ?? 'register'));
if (!in_array($type, ['register', 'forgot'], true)) {
    $type = 'register';
}

$pageTitle = $type === 'forgot' ? 'Reset Password' : 'Verify Email';
$error = '';
$info = '';

// Determine active email from session or query or post
$email = '';
if ($type === 'register') {
    $email = trim($_POST['email'] ?? ($_SESSION['verify_email'] ?? ($_GET['email'] ?? '')));
} else {
    $email = trim($_POST['email'] ?? ($_SESSION['reset_email'] ?? ($_GET['email'] ?? '')));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? 'verify';
    $submittedEmail = trim(filter_var($_POST['email'] ?? $email, FILTER_SANITIZE_EMAIL));

    if (empty($submittedEmail) || !filter_var($submittedEmail, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please provide a valid email address.';
    } else {
        $email = $submittedEmail;

        // Action: Resend OTP
        if ($action === 'resend') {
            $stmt = $pdo->prepare('SELECT user_id, name, email, role, is_verified FROM users WHERE email = ?');
            $stmt->execute([$email]);
            $user = $stmt->fetch();

            if (!$user) {
                $error = 'No account found with that email address.';
            } else {
                $newOtp = str_pad((string)random_int(100000, 999999), 6, '0', STR_PAD_LEFT);
                $updateStmt = $pdo->prepare('
                    UPDATE users 
                    SET otp_code = ?, otp_expiry = DATE_ADD(NOW(), INTERVAL 15 MINUTE) 
                    WHERE user_id = ?
                ');
                $updateStmt->execute([$newOtp, $user['user_id']]);

                $mailType = ($type === 'forgot') ? 'password_reset' : 'verification';
                send_otp_email($user['email'], $user['name'], $newOtp, $mailType);

                if ($type === 'register') {
                    $_SESSION['verify_email'] = $email;
                } else {
                    $_SESSION['reset_email'] = $email;
                }

                $info = 'A new 6-digit code has been sent to your email.';
            }
        } 
        // Action: Verify OTP
        elseif ($action === 'verify') {
            $otp = trim($_POST['otp'] ?? '');

            if (!preg_match('/^[0-9]{6}$/', $otp)) {
                $error = 'Please enter a valid 6-digit numerical OTP code.';
            } else {
                $newPassword     = $_POST['password'] ?? '';
                $confirmPassword = $_POST['confirm_password'] ?? '';

                if ($type === 'forgot') {
                    if (strlen($newPassword) < 6) {
                        $error = 'New password must be at least 6 characters.';
                    } elseif ($newPassword !== $confirmPassword) {
                        $error = 'Passwords do not match.';
                    }
                }

                if (empty($error)) {
                    $stmt = $pdo->prepare('
                        SELECT user_id, name, email, role, is_verified, otp_code, otp_expiry 
                        FROM users 
                        WHERE email = ?
                    ');
                    $stmt->execute([$email]);
                    $user = $stmt->fetch();

                    if (!$user) {
                        $error = 'No account found with this email.';
                    } elseif (empty($user['otp_code'])) {
                        $error = 'No active OTP found. Please request a new verification code.';
                    } elseif ($user['otp_code'] !== $otp) {
                        $error = 'Invalid OTP code. Please check and try again.';
                    } elseif (strtotime($user['otp_expiry']) < time()) {
                        $error = 'This OTP has expired. Please request a new verification code.';
                    } else {
                        // OTP is valid!
                        if ($type === 'register') {
                            $updateStmt = $pdo->prepare('
                                UPDATE users 
                                SET is_verified = 1, otp_code = NULL, otp_expiry = NULL 
                                WHERE user_id = ?
                            ');
                            $updateStmt->execute([$user['user_id']]);

                            unset($_SESSION['verify_email'], $_SESSION['dev_otp']);
                            header('Location: ' . BASE_URL . 'auth/login.php?verified=1');
                            exit;
                        } else {
                            // Password reset
                            $hashed = password_hash($newPassword, PASSWORD_DEFAULT);
                            $updateStmt = $pdo->prepare('
                                UPDATE users 
                                SET password = ?, otp_code = NULL, otp_expiry = NULL 
                                WHERE user_id = ?
                            ');
                            $updateStmt->execute([$hashed, $user['user_id']]);

                            $userRole = $user['role'];
                            unset($_SESSION['reset_email'], $_SESSION['reset_role'], $_SESSION['dev_otp']);

                            if ($userRole === 'admin') {
                                header('Location: ' . BASE_URL . 'auth/admin_login.php?reset=1');
                            } else {
                                header('Location: ' . BASE_URL . 'auth/login.php?reset=1');
                            }
                            exit;
                        }
                    }
                }
            }
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
                    <h3 class="fw-bold mb-1">
                        <?= $type === 'forgot' ? 'Reset Password' : 'Verify Your Email' ?>
                    </h3>
                    <p class="text-body-secondary small mb-0">
                        <?php if ($email): ?>
                            Enter the 6-digit code sent to <strong><?= htmlspecialchars($email) ?></strong>
                        <?php else: ?>
                            Enter your registered email and 6-digit verification code.
                        <?php endif; ?>
                    </p>
                </div>

                <?php if (!empty($_SESSION['dev_otp'])): ?>
                    <div class="alert alert-info border-info-subtle d-flex align-items-center justify-content-between mb-3" role="alert">
                        <div class="small">
                            <strong>Dev Mode:</strong> SMTP unconfigured.<br>
                            Your OTP is: <span class="badge bg-primary fs-6 font-monospace"><?= htmlspecialchars($_SESSION['dev_otp']) ?></span>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if ($info): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <?= htmlspecialchars($info) ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>

                <?php if ($error): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <?= htmlspecialchars($error) ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>

                <form method="POST" action="<?= BASE_URL ?>auth/verify_otp.php?type=<?= urlencode($type) ?>" novalidate>
                    <input type="hidden" name="action" value="verify">
                    <input type="hidden" name="type" value="<?= htmlspecialchars($type) ?>">

                    <?php if (empty($email)): ?>
                        <div class="mb-3">
                            <label for="email" class="form-label">Email Address</label>
                            <input type="email" class="form-control" id="email" name="email" 
                                   placeholder="name@example.com" required value="<?= htmlspecialchars($email) ?>">
                        </div>
                    <?php else: ?>
                        <input type="hidden" name="email" value="<?= htmlspecialchars($email) ?>">
                    <?php endif; ?>

                    <div class="mb-3">
                        <label for="otp" class="form-label fw-semibold">6-Digit OTP Code</label>
                        <input type="text" 
                               class="form-control text-center fs-3 fw-bold font-monospace py-2" 
                               id="otp" 
                               name="otp" 
                               maxlength="6" 
                               pattern="[0-9]{6}" 
                               inputmode="numeric" 
                               autocomplete="one-time-code" 
                               placeholder="------" 
                               style="letter-spacing: 0.35em;"
                               required autofocus>
                        <div class="form-text text-center">Codes are valid for 15 minutes.</div>
                    </div>

                    <?php if ($type === 'forgot'): ?>
                        <div class="mb-3">
                            <label for="password" class="form-label fw-semibold">New Password</label>
                            <input type="password" class="form-control" id="password" name="password" minlength="6" placeholder="At least 6 characters" required>
                        </div>

                        <div class="mb-4">
                            <label for="confirm_password" class="form-label fw-semibold">Confirm New Password</label>
                            <input type="password" class="form-control" id="confirm_password" name="confirm_password" minlength="6" placeholder="Repeat new password" required>
                        </div>
                    <?php endif; ?>

                    <button type="submit" class="btn btn-primary w-100 py-2 fw-semibold mb-3">
                        <?= $type === 'forgot' ? 'Update Password' : 'Verify &amp; Activate' ?>
                    </button>
                </form>

                <!-- Resend OTP Form -->
                <form method="POST" action="<?= BASE_URL ?>auth/verify_otp.php?type=<?= urlencode($type) ?>" class="text-center">
                    <input type="hidden" name="action" value="resend">
                    <input type="hidden" name="type" value="<?= htmlspecialchars($type) ?>">
                    <input type="hidden" name="email" value="<?= htmlspecialchars($email) ?>">
                    <span class="text-body-secondary small">Didn't receive the code?</span>
                    <button type="submit" class="btn btn-link btn-sm p-0 align-baseline text-decoration-none fw-semibold">
                        Resend Code
                    </button>
                </form>

                <hr class="my-4">

                <div class="d-flex flex-column gap-2 text-center small">
                    <div>
                        <a href="<?= BASE_URL ?>auth/login.php" class="text-decoration-none">
                            &larr; Back to Login
                        </a>
                    </div>
                    <?php if ($type === 'register'): ?>
                        <div>
                            <span class="text-body-secondary">Wrong email?</span>
                            <a href="<?= BASE_URL ?>auth/register.php" class="text-decoration-none">Register again</a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
