<?php
// includes/conversion_guard.php
//
// ══════════════════════════════════════════════════════════════
//  HOW TO USE
//  Add ONE line to the very top of every converter PHP file:
//
//     require_once __DIR__ . '/includes/conversion_guard.php';
//
//  That's it. This file handles everything else.
// ══════════════════════════════════════════════════════════════

require_once __DIR__ . '/auth.php';  // boots session + helpers

// 1. Guest users → redirect to login
if (!is_logged_in()) {
    $back = APP_URL . $_SERVER['REQUEST_URI'];
    header('Location: ' . APP_URL . '/auth/login.php?redirect=' . urlencode($back));
    exit;
}

// 2. Free users who hit their limit → redirect to upgrade
if (has_reached_limit()) {
    header('Location: ' . APP_URL . '/subscription/upgrade.php?limit=1');
    exit;
}

// 3. (Optional) Inject the conversion banner into the page.
//    Called from within the page HTML: <?php echo conversion_banner(); ?>
function conversion_banner(): string {
    $user      = current_user();
    if (!$user || $user['plan'] !== 'free') return '';

    $remaining = conversions_remaining();
    $total     = FREE_CONVERSIONS_LIMIT;

    $pct   = round((($total - $remaining) / $total) * 100);
    $color = $remaining === 1 ? '#ef4444' : ($remaining === 2 ? '#f59e0b' : '#10b981');

    return <<<HTML
<div id="mc-conversion-banner" style="
    background:#1e293b;color:#f1f5f9;
    font-family:system-ui,sans-serif;font-size:13px;
    padding:10px 20px;display:flex;align-items:center;
    justify-content:space-between;gap:12px;flex-wrap:wrap;
    border-bottom:1px solid #334155;">

    <div style="display:flex;align-items:center;gap:10px;">
        <span>Free plan:</span>
        <div style="width:120px;height:6px;background:#334155;border-radius:3px;overflow:hidden;">
            <div style="width:{$pct}%;height:100%;background:{$color};border-radius:3px;transition:width .4s;"></div>
        </div>
        <span style="color:{$color};font-weight:600;">{$remaining} of {$total} conversions left</span>
    </div>

    <a href="{APP_URL}/subscription/upgrade.php" style="
        background:#6366f1;color:#fff;padding:5px 14px;
        border-radius:6px;text-decoration:none;font-weight:600;
        font-size:12px;white-space:nowrap;">
        Upgrade ↗
    </a>
</div>
HTML;
}
