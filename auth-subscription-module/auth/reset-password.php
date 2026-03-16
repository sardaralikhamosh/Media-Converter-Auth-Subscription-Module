<?php
// auth/reset-password.php
require_once __DIR__ . '/../includes/auth.php';

$token = $_GET['token'] ?? '';
$error = '';
$done  = false;

$stmt = db()->prepare('SELECT * FROM users WHERE reset_token = ? AND reset_expires > NOW()');
$stmt->execute([$token]);
$user = $stmt->fetch();

if (!$user) {
    $error = 'This reset link is invalid or has expired. Please request a new one.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $user) {
    $password = $_POST['password'] ?? '';
    $confirm  = $_POST['confirm'] ?? '';

    if (strlen($password) < 8) {
        $error = 'Password must be at least 8 characters.';
    } elseif ($password !== $confirm) {
        $error = 'Passwords do not match.';
    } else {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        db()->prepare('UPDATE users SET password = ?, reset_token = NULL, reset_expires = NULL WHERE id = ?')
            ->execute([$hash, $user['id']]);
        $done = true;
        flash('success', 'Password updated successfully. Please sign in.');
        header('Location: ' . APP_URL . '/auth/login.php');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>New Password – <?= APP_NAME ?></title>
<link rel="stylesheet" href="<?= APP_URL ?>/auth/assets/auth.css">
</head>
<body class="auth-page">

<div class="auth-card">
    <a class="auth-logo" href="<?= APP_URL ?>">
        <span class="logo-icon">⚡</span>
        <span><?= APP_NAME ?></span>
    </a>

    <h1 class="auth-title">Set new password</h1>

    <?php if ($error): ?>
        <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
        <?php if (!$user): ?>
            <p class="auth-footer"><a href="<?= APP_URL ?>/auth/forgot-password.php">Request a new link</a></p>
        <?php endif; ?>
    <?php else: ?>
    <form method="POST" action="">
        <div class="form-group">
            <label for="password">New password</label>
            <input type="password" id="password" name="password" placeholder="••••••••" required>
        </div>
        <div class="form-group">
            <label for="confirm">Confirm password</label>
            <input type="password" id="confirm" name="confirm" placeholder="••••••••" required>
        </div>
        <button type="submit" class="btn-primary">Update Password</button>
    </form>
    <?php endif; ?>
</div>

</body>
</html>
