<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../config/mail.php';
require_once __DIR__ . '/PHPMailer/Exception.php';
require_once __DIR__ . '/PHPMailer/PHPMailer.php';
require_once __DIR__ . '/PHPMailer/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

/**
 * Sends a 6-digit OTP email using PHPMailer.
 * 
 * @param string $toEmail Recipient email address
 * @param string $toName  Recipient full name
 * @param string $otp     6-digit verification code
 * @param string $type    'verification' or 'password_reset'
 * @return array ['success' => bool, 'error' => string, 'otp' => string]
 */
function send_otp_email(string $toEmail, string $toName, string $otp, string $type = 'verification'): array {
    // If SMTP is unconfigured placeholder, save debug OTP for seamless local testing
    if (SMTP_USER === 'your_email@gmail.com' || SMTP_PASS === 'your_gmail_app_password') {
        $_SESSION['dev_otp'] = $otp;
        return [
            'success' => false,
            'dev_mode' => true,
            'error' => 'SMTP credentials not configured in config/mail.php. (Dev Mode Active: Use OTP shown on screen)',
            'otp' => $otp
        ];
    }

    $mail = new PHPMailer(true);

    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host       = SMTP_HOST;
        $mail->SMTPAuth   = true;
        $mail->Username   = SMTP_USER;
        $mail->Password   = SMTP_PASS;
        $mail->SMTPSecure = SMTP_SECURE;
        $mail->Port       = SMTP_PORT;

        // Recipients
        $mail->setFrom(MAIL_FROM_EMAIL, MAIL_FROM_NAME);
        $mail->addAddress($toEmail, $toName);

        // Content
        $mail->isHTML(true);
        if ($type === 'password_reset') {
            $mail->Subject = 'Password Reset OTP - Student Feedback System';
            $mail->Body    = "
                <div style='font-family: Arial, sans-serif; max-width: 500px; margin: auto; padding: 24px; border: 1px solid #e0e0e0; border-radius: 8px;'>
                    <h2 style='color: #0d6efd;'>Password Reset Request</h2>
                    <p>Hello <strong>" . htmlspecialchars($toName) . "</strong>,</p>
                    <p>You requested to reset your password. Use the verification code below to proceed:</p>
                    <div style='background: #f8f9fa; padding: 16px; text-align: center; border-radius: 6px; font-size: 28px; font-weight: bold; letter-spacing: 6px; color: #198754;'>
                        $otp
                    </div>
                    <p style='color: #6c757d; font-size: 13px; margin-top: 20px;'>This code will expire in 15 minutes. If you did not request this, you can safely ignore this email.</p>
                </div>
            ";
            $mail->AltBody = "Hello $toName, Your password reset OTP is: $otp (valid for 15 minutes).";
        } else {
            $mail->Subject = 'Verify Your Account - Student Feedback System';
            $mail->Body    = "
                <div style='font-family: Arial, sans-serif; max-width: 500px; margin: auto; padding: 24px; border: 1px solid #e0e0e0; border-radius: 8px;'>
                    <h2 style='color: #0d6efd;'>Account Verification</h2>
                    <p>Hello <strong>" . htmlspecialchars($toName) . "</strong>,</p>
                    <p>Thank you for registering. Please use the 6-digit verification code below to verify your email address:</p>
                    <div style='background: #f8f9fa; padding: 16px; text-align: center; border-radius: 6px; font-size: 28px; font-weight: bold; letter-spacing: 6px; color: #0d6efd;'>
                        $otp
                    </div>
                    <p style='color: #6c757d; font-size: 13px; margin-top: 20px;'>This code will expire in 15 minutes.</p>
                </div>
            ";
            $mail->AltBody = "Hello $toName, Your account verification OTP is: $otp (valid for 15 minutes).";
        }

        $mail->send();
        unset($_SESSION['dev_otp']);
        return ['success' => true, 'error' => '', 'otp' => $otp];

    } catch (Exception $e) {
        // Fallback for dev mode if SMTP fails
        $_SESSION['dev_otp'] = $otp;
        return [
            'success' => false,
            'dev_mode' => true,
            'error' => 'Mail delivery failed: ' . $mail->ErrorInfo . '. (Dev Mode Active: Use OTP shown on screen)',
            'otp' => $otp
        ];
    }
}
