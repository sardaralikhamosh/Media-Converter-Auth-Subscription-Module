<?php
// subscription/checkout.php
// This is a placeholder checkout page.
// To integrate real payments, add your Stripe/PayPal keys below.
require_once __DIR__ . '/../includes/auth.php';
require_login();

$validPlans = ['pro', 'business'];
$plan       = $_GET['plan'] ?? '';

if (!in_array($plan, $validPlans, true)) {
    header('Location: ' . APP_URL . '/subscription/upgrade.php');
    exit;
}

$planInfo = PLANS[$plan];
$user     = current_user();

// ── Simulate payment success (remove this block and add real payment gateway) ──
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['simulate_pay'])) {
    $expires = date('Y-m-d H:i:s', strtotime('+30 days'));
    $pdo     = db();

    // Update user plan
    $pdo->prepare('UPDATE users SET plan = ?, plan_expires = ? WHERE id = ?')
        ->execute([$plan, $expires, $user['id']]);

    // Log subscription
    $pdo->prepare(
        'INSERT INTO subscriptions (user_id, plan, amount, expires_at) VALUES (?, ?, ?, ?)'
    )->execute([$user['id'], $plan, $planInfo['price'], $expires]);

    flash('success', "🎉 You're now on the {$planInfo['label']} plan! Enjoy unlimited conversions.");
    header('Location: ' . APP_URL . '/subscription/upgrade.php');
    exit;
}
// ── End simulate block ──
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Checkout – <?= APP_NAME ?></title>
<link rel="stylesheet" href="<?= APP_URL ?>/auth/assets/auth.css">
<link rel="stylesheet" href="<?= APP_URL ?>/auth/assets/upgrade.css">
</head>
<body class="auth-page">

<div class="auth-card checkout-card">
    <a class="auth-logo" href="<?= APP_URL ?>">
        <span class="logo-icon">⚡</span>
        <span><?= APP_NAME ?></span>
    </a>

    <h1 class="auth-title">Complete your upgrade</h1>

    <div class="checkout-summary">
        <div class="checkout-plan">
            <span class="checkout-plan-name"><?= $planInfo['label'] ?> Plan</span>
            <span class="checkout-plan-price">$<?= number_format($planInfo['price'], 2) ?>/month</span>
        </div>
        <ul class="checkout-features">
            <li>✔ Unlimited conversions</li>
            <li>✔ Priority processing</li>
            <li>✔ 30-day billing cycle</li>
            <li>✔ Cancel anytime</li>
        </ul>
    </div>

    <!-- ═══════════════════════════════════════════════
         REPLACE THIS FORM WITH YOUR PAYMENT GATEWAY
         Options: Stripe Checkout, PayPal, Paddle, etc.
         ═══════════════════════════════════════════════ -->
    <div class="payment-placeholder">
        <p class="payment-note">💳 Payment gateway not yet configured.</p>
        <p class="payment-note-sub">
            Integrate Stripe or PayPal here. The simulate button below
            can be used for testing during development.
        </p>

        <!-- STRIPE EXAMPLE (uncomment when ready):
        <script src="https://js.stripe.com/v3/"></script>
        <button id="stripe-btn" class="btn-primary">Pay with Stripe</button>
        -->
    </div>

    <!-- DEVELOPMENT ONLY: simulate payment -->
    <form method="POST" action="">
        <button type="submit" name="simulate_pay" value="1" class="btn-primary"
                onclick="return confirm('Simulate successful payment for testing?')">
            ⚙ Simulate Payment (Dev Only)
        </button>
    </form>
    <!-- /DEVELOPMENT ONLY -->

    <p class="auth-footer">
        <a href="<?= APP_URL ?>/subscription/upgrade.php">← Back to Plans</a>
    </p>
</div>

</body>
</html>
