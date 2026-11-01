<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Queue a one-time alert to show after a redirect.
 * $type is a Bootstrap alert type: success, danger, warning, info.
 */
function set_flash(string $type, string $message): void {
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function has_flash(): bool {
    return isset($_SESSION['flash']);
}

/**
 * Returns the queued flash message and clears it, or null if none.
 */
function get_flash(): ?array {
    $flash = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);
    return $flash;
}
