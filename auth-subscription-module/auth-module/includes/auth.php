<?php
// includes/auth.php
// Core authentication helpers – include this everywhere.

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';

// ── Session bootstrap ────────────────────────────────────────
if (session_status() === PHP_SESSION_NONE) {
    session_name(SESSION_NAME);
    session_start();
}

// ── Helpers ──────────────────────────────────────────────────

/** Returns the logged-in user row or null. */
function current_user(): ?array {
    if (empty($_SESSION['user_id'])) return null;
    static $cache = [];
    $id = (int)$_SESSION['user_id'];
    if (!isset($cache[$id])) {
        $stmt = db()->prepare('SELECT * FROM users WHERE id = ?');
        $stmt->execute([$id]);
        $cache[$id] = $stmt->fetch() ?: null;
    }
    return $cache[$id];
}

/** Returns true if user is logged in. */
function is_logged_in(): bool {
    return current_user() !== null;
}

/** Redirect to login page if not logged in. */
function require_login(string $redirect = ''): void {
    if (!is_logged_in()) {
        $back = $redirect ?: (APP_URL . $_SERVER['REQUEST_URI']);
        header('Location: ' . APP_URL . '/auth/login.php?redirect=' . urlencode($back));
        exit;
    }
}

/** Log a user in by setting the session. */
function login_user(array $user): void {
    session_regenerate_id(true);
    $_SESSION['user_id'] = $user['id'];
}

/** Destroy the session (logout). */
function logout_user(): void {
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $p = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $p['path'], $p['domain'], $p['secure'], $p['httponly']);
    }
    session_destroy();
}

/** Check if user has reached free conversion limit. */
function has_reached_limit(): bool {
    $user = current_user();
    if (!$user) return false; // guest – handled separately
    if ($user['plan'] !== 'free') return false; // paid plans are unlimited

    // Reset daily counter if last conversion was on a different day
    // (We track *total* conversions for free; change logic here if you prefer daily)
    return (int)$user['conversions_total'] >= FREE_CONVERSIONS_LIMIT;
}

/** Record a conversion and bump the counter. Returns false if limit reached. */
function record_conversion(string $from, string $to, string $fileName = '', int $fileSize = 0): bool {
    $user = current_user();
    if (!$user) return true; // guests: no tracking (or add IP-based tracking)

    if (has_reached_limit()) return false;

    $pdo = db();

    // Log entry
    $stmt = $pdo->prepare(
        'INSERT INTO conversion_logs (user_id, from_format, to_format, file_name, file_size, ip_address)
         VALUES (?, ?, ?, ?, ?, ?)'
    );
    $stmt->execute([
        $user['id'], $from, $to,
        $fileName ?: null,
        $fileSize ?: null,
        $_SERVER['REMOTE_ADDR'] ?? null,
    ]);

    // Bump counter
    $pdo->prepare('UPDATE users SET conversions_total = conversions_total + 1, last_conversion_date = CURDATE() WHERE id = ?')
        ->execute([$user['id']]);

    // Clear static cache so next call to current_user() re-fetches
    return true;
}

/** How many free conversions remain for the current user. */
function conversions_remaining(): int {
    $user = current_user();
    if (!$user || $user['plan'] !== 'free') return PHP_INT_MAX;
    return max(0, FREE_CONVERSIONS_LIMIT - (int)$user['conversions_total']);
}

/** Flash message helpers. */
function flash(string $key, string $msg): void {
    $_SESSION['flash'][$key] = $msg;
}
function get_flash(string $key): string {
    $msg = $_SESSION['flash'][$key] ?? '';
    unset($_SESSION['flash'][$key]);
    return $msg;
}
