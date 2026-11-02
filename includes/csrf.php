<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/** Token for the current session, created on first use. */
function csrf_token(): string {
    return $_SESSION['csrf_token'] ??= bin2hex(random_bytes(16));
}

/** True when the POSTed token matches the session token. */
function csrf_valid(): bool {
    return hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'] ?? '');
}
