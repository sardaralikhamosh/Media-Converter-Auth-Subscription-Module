<?php
// auth/forgot-password.php
require_once __DIR__ . '/../includes/auth.php';

$msg   = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } else {
        $pdo  = db();
        $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ?');
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user) {
            $token   = bin2hex(random_bytes(32));
            $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));
            $pdo->prepare('UPDATE users SET reset_token = ?, reset_expires = ? WHERE id = ?')
                ->execute([$token, $expires, $user['id']]);

            $link    = APP_URL . '/auth/reset-password.php?token=' . $token;
            $subject = 'Reset your ' . APP_NAME . ' password';
            $body    = "Click the link below to reset your password (valid 1 hour):\n\n$link";
            mail($email, $subject, $body, 'From: ' . APP_EMAIL);
        }

        // Always show success to prevent email enumeration
        $msg = 'If that email exists, a reset link has been sent.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Reset Password – <?= APP_NAME ?></title>
<link rel="stylesheet" href="<?= APP_URL ?>/auth/assets/auth.css">
</head>
<body class="auth-page">

<div class="auth-card">
    <a class="auth-logo" href="<?= APP_URL ?>">
        <span class="logo-icon">⚡</span>
        <span><?= APP_NAME ?></span>
    </a>

    <h1 class="auth-title">Reset password</h1>
    <p class="auth-sub">We'll email you a link to reset your password.</p>

    <?php if ($error): ?>
        <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <?php if ($msg): ?>
        <div class="alert alert-success"><?= htmlspecialchars($msg) ?></div>
    <?php endif; ?>

    <form method="POST" action="">
        <div class="form-group">
            <label for="email">Email address</label>
            <input type="email" id="email" name="email" placeholder="you@example.com" required>
        </div>
        <button type="submit" class="btn-primary">Send Reset Link</button>
    </form>

    <p class="auth-footer">
        <a href="<?= APP_URL ?>/auth/login.php">← Back to Sign In</a>
    </p>
</div>

</body>
</html>
