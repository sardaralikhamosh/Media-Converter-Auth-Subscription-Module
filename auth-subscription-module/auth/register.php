<?php
// auth/register.php
require_once __DIR__ . '/../includes/auth.php';

if (is_logged_in()) {
    header('Location: ' . APP_URL . '/index.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name     = trim($_POST['name'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm  = $_POST['confirm'] ?? '';

    if (!$name || !$email || !$password || !$confirm) {
        $error = 'Please fill in all fields.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } elseif (strlen($password) < 8) {
        $error = 'Password must be at least 8 characters.';
    } elseif ($password !== $confirm) {
        $error = 'Passwords do not match.';
    } else {
        $pdo  = db();
        $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ?');
        $stmt->execute([$email]);

        if ($stmt->fetch()) {
            $error = 'An account with that email already exists.';
        } else {
            $hash  = password_hash($password, PASSWORD_DEFAULT);
            $token = bin2hex(random_bytes(32));

            $ins = $pdo->prepare(
                'INSERT INTO users (name, email, password, verify_token) VALUES (?, ?, ?, ?)'
            );
            $ins->execute([$name, $email, $hash, $token]);
            $userId = $pdo->lastInsertId();

            // Auto-login after registration
            login_user(['id' => $userId]);

            flash('success', 'Account created! You have 3 free conversions to start.');
            header('Location: ' . APP_URL . '/index.php');
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Create Account – <?= APP_NAME ?></title>
<link rel="stylesheet" href="<?= APP_URL ?>/auth/assets/auth.css">
</head>
<body class="auth-page">

<div class="auth-card">
    <a class="auth-logo" href="<?= APP_URL ?>">
        <span class="logo-icon">⚡</span>
        <span><?= APP_NAME ?></span>
    </a>

    <h1 class="auth-title">Create your account</h1>
    <p class="auth-sub">Start with 3 free conversions — no credit card needed</p>

    <?php if ($error): ?>
        <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST" action="">
        <div class="form-group">
            <label for="name">Full name</label>
            <input type="text" id="name" name="name" placeholder="Jane Smith"
                   value="<?= htmlspecialchars($_POST['name'] ?? '') ?>" required>
        </div>

        <div class="form-group">
            <label for="email">Email address</label>
            <input type="email" id="email" name="email" placeholder="you@example.com"
                   value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
        </div>

        <div class="form-group">
            <label for="password">Password <small>(min. 8 chars)</small></label>
            <input type="password" id="password" name="password" placeholder="••••••••" required>
        </div>

        <div class="form-group">
            <label for="confirm">Confirm password</label>
            <input type="password" id="confirm" name="confirm" placeholder="••••••••" required>
        </div>

        <button type="submit" class="btn-primary">Create Free Account</button>
    </form>

    <div class="plan-pills">
        <span class="pill">✔ 3 free conversions</span>
        <span class="pill">✔ No credit card</span>
        <span class="pill">✔ Instant access</span>
    </div>

    <p class="auth-footer">
        Already have an account?
        <a href="<?= APP_URL ?>/auth/login.php">Sign in</a>
    </p>
</div>

</body>
</html>
