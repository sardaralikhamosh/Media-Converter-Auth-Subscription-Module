<?php
// subscription/upgrade.php
require_once __DIR__ . '/../includes/auth.php';
require_login();  // must be logged in to upgrade

$user    = current_user();
$success = get_flash('success');
$plans   = PLANS;
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Upgrade Plan – <?= APP_NAME ?></title>
<link rel="stylesheet" href="<?= APP_URL ?>/auth/assets/auth.css">
<link rel="stylesheet" href="<?= APP_URL ?>/auth/assets/upgrade.css">
</head>
<body class="upgrade-page">

<!-- Nav strip -->
<nav class="upgrade-nav">
    <a class="auth-logo" href="<?= APP_URL ?>">
        <span class="logo-icon">⚡</span>
        <span><?= APP_NAME ?></span>
    </a>
    <div class="nav-right">
        <span class="user-pill">👤 <?= htmlspecialchars($user['name']) ?></span>
        <a href="<?= APP_URL ?>/auth/logout.php" class="btn-outline-sm">Sign Out</a>
    </div>
</nav>

<main class="upgrade-main">

    <!-- Limit banner (shown when redirected from conversion) -->
    <?php if (isset($_GET['limit'])): ?>
    <div class="limit-banner">
        <span class="limit-icon">🚫</span>
        <div>
            <strong>You've used all 3 free conversions.</strong>
            Upgrade to keep converting without limits.
        </div>
    </div>
    <?php endif; ?>

    <?php if ($success): ?>
        <div class="alert alert-success center"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>

    <div class="upgrade-hero">
        <h1>Choose your plan</h1>
        <p>Unlock unlimited conversions and premium features</p>
    </div>

    <!-- Plan cards -->
    <div class="plan-grid">

        <!-- Free -->
        <div class="plan-card <?= $user['plan'] === 'free' ? 'plan-current' : '' ?>">
            <div class="plan-badge">Current</div>
            <div class="plan-name">Free</div>
            <div class="plan-price"><span class="price-amount">$0</span><span class="price-period">/forever</span></div>
            <ul class="plan-features">
                <li>✔ 3 total conversions</li>
                <li>✔ All formats supported</li>
                <li>✗ Unlimited conversions</li>
                <li>✗ Priority processing</li>
                <li>✗ Larger file sizes</li>
            </ul>
            <?php if ($user['plan'] === 'free'): ?>
                <button class="btn-plan btn-disabled" disabled>Your current plan</button>
            <?php else: ?>
                <button class="btn-plan btn-disabled" disabled>Downgrade</button>
            <?php endif; ?>
        </div>

        <!-- Pro (highlighted) -->
        <div class="plan-card plan-featured <?= $user['plan'] === 'pro' ? 'plan-current' : '' ?>">
            <div class="plan-badge plan-badge-pro">Most Popular</div>
            <div class="plan-name">Pro</div>
            <div class="plan-price"><span class="price-amount">$9.99</span><span class="price-period">/month</span></div>
            <ul class="plan-features">
                <li>✔ Unlimited conversions</li>
                <li>✔ All formats supported</li>
                <li>✔ Priority processing</li>
                <li>✔ Up to 100 MB files</li>
                <li>✗ API access</li>
            </ul>
            <?php if ($user['plan'] === 'pro'): ?>
                <button class="btn-plan btn-disabled" disabled>Your current plan</button>
            <?php else: ?>
                <a href="<?= APP_URL ?>/subscription/checkout.php?plan=pro" class="btn-plan btn-pro">Upgrade to Pro</a>
            <?php endif; ?>
        </div>

        <!-- Business -->
        <div class="plan-card <?= $user['plan'] === 'business' ? 'plan-current' : '' ?>">
            <div class="plan-badge">Team &amp; API</div>
            <div class="plan-name">Business</div>
            <div class="plan-price"><span class="price-amount">$24.99</span><span class="price-period">/month</span></div>
            <ul class="plan-features">
                <li>✔ Unlimited conversions</li>
                <li>✔ All formats supported</li>
                <li>✔ Priority processing</li>
                <li>✔ Up to 500 MB files</li>
                <li>✔ API access + webhooks</li>
            </ul>
            <?php if ($user['plan'] === 'business'): ?>
                <button class="btn-plan btn-disabled" disabled>Your current plan</button>
            <?php else: ?>
                <a href="<?= APP_URL ?>/subscription/checkout.php?plan=business" class="btn-plan btn-business">Upgrade to Business</a>
            <?php endif; ?>
        </div>

    </div><!-- /.plan-grid -->

    <p class="upgrade-note">Cancel anytime. No contracts. Billed monthly.</p>

</main>

</body>
</html>
