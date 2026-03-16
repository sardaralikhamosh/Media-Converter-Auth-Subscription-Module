<?php
// auth/login.php
require_once __DIR__ . '/../includes/auth.php';

if (is_logged_in()) {
    header('Location: ' . APP_URL . '/index.php');
    exit;
}

$error   = '';
$success = get_flash('success');
$redirect = $_GET['redirect'] ?? (APP_URL . '/index.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (!$email || !$password) {
        $error = 'Please fill in all fields.';
    } else {
        $stmt = db()->prepare('SELECT * FROM users WHERE email = ?');
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            login_user($user);
            header('Location: ' . $redirect);
            exit;
        } else {
            $error = 'Invalid email or password.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Sign In – <?= APP_NAME ?></title>
<link rel="stylesheet" href="<?= APP_URL ?>/auth/assets/auth.css">
</head>
<body class="auth-page">

<div class="auth-card">
    <a class="auth-logo" href="<?= APP_URL ?>">
        <span class="logo-icon">⚡</span>
        <span><?= APP_NAME ?></span>
    </a>

    <h1 class="auth-title">Welcome back</h1>
    <p class="auth-sub">Sign in to continue converting files</p>

    <?php if ($error): ?>
        <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <?php if ($success): ?>
        <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>

    <form method="POST" action="">
        <input type="hidden" name="redirect" value="<?= htmlspecialchars($redirect) ?>">

        <div class="form-group">
            <label for="email">Email address</label>
            <input type="email" id="email" name="email" placeholder="you@example.com"
                   value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
        </div>

        <div class="form-group">
            <label for="password">Password
                <a class="forgot-link" href="<?= APP_URL ?>/auth/forgot-password.php">Forgot?</a>
            </label>
            <input type="password" id="password" name="password" placeholder="••••••••" required>
        </div>

        <button type="submit" class="btn-primary">Sign In</button>
    </form>

    <p class="auth-footer">
        Don't have an account?
        <a href="<?= APP_URL ?>/auth/register.php">Create one free</a>
    </p>
</div>

</body>
</html>
