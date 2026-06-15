<?php
// =============================================================
// iSkout — includes/helpers.php
// Reusable utility functions used across all pages.
// =============================================================

/**
 * Sanitize user input for safe HTML output.
 */
function sanitize(string $input): string {
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

/**
 * Validate that an email ends with the PUP student domain.
 */
function is_valid_pup_email(string $email): bool {
    $email = strtolower(trim($email));
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return false;
    }

    $patterns = [];
    if (defined('PUP_EMAIL_DOMAINS') && PUP_EMAIL_DOMAINS !== '') {
        $patterns = array_filter(array_map('trim', explode(',', strtolower(PUP_EMAIL_DOMAINS))));
    }

    if (empty($patterns)) {
        $patterns = [strtolower(PUP_EMAIL_DOMAIN), '@*.pup.edu.ph'];
    }

    foreach ($patterns as $pattern) {
        if ($pattern === '') {
            continue;
        }

        if (strpos($pattern, '*') !== false) {
            $regex = '/'.str_replace('\\*', '.*', preg_quote($pattern, '/')).'$/';
            if (preg_match($regex, $email)) {
                return true;
            }
        } elseif (str_ends_with($email, $pattern)) {
            return true;
        }
    }

    return false;
}

/**
 * Generate a random 4-digit OTP as a zero-padded string.
 */
function generate_otp(): string {
    return str_pad((string)random_int(0, 9999), 4, '0', STR_PAD_LEFT);
}

/**
 * Format a MySQL TIME value (HH:MM:SS) to a human-readable 12-hour string.
 */
function format_time(?string $time): string {
    if (!$time) return 'N/A';
    return date('g:i A', strtotime($time));
}

/**
 * Determine if a listing is currently open based on its open/close times.
 */
function is_open_now(?string $open_time, ?string $close_time): bool {
    if (!$open_time || !$close_time) return false;
    $now   = strtotime(date('H:i:s'));
    $open  = strtotime($open_time);
    $close = strtotime($close_time);
    return ($now >= $open && $now <= $close);
}

/**
 * Get a URL-safe redirect path relative to APP_URL.
 */
function redirect(string $path): void {
    header('Location: ' . APP_URL . '/' . ltrim($path, '/'));
    exit;
}

/**
 * Set a flash message in the session (used for one-time success/error notices).
 */
function set_flash(string $type, string $message): void {
    $_SESSION['flash'] = ['type' => $type, 'msg' => $message];
}

/**
 * Retrieve and clear the flash message from the session.
 */
function get_flash(): ?array {
    if (!empty($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

/**
 * Render an HTML flash alert block if a flash message exists.
 */
function render_flash(): void {
    $flash = get_flash();
    if (!$flash) return;
    $type    = $flash['type'] === 'success' ? 'success' : 'error';
    $icon    = $type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle';
    $message = sanitize($flash['msg']);
    echo "<div class=\"flash-alert flash-{$type}\"><i class=\"fas {$icon}\"></i> {$message}</div>";
}

/**
 * Generate a secure remember-me token (64 hex chars).
 */
function generate_remember_token(): string {
    return bin2hex(random_bytes(32));
}
