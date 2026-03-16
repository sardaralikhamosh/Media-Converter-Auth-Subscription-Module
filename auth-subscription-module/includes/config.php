<?php
// ============================================================
// auth/config.php  –  drop this file in your project root
// ============================================================

// ── Database ────────────────────────────────────────────────
define('DB_HOST', 'localhost');
define('DB_NAME', 'u194258631_mediaconverter');   // change if needed
define('DB_USER', 'u194258631_mediaconverter');                 // change to your DB user
define('DB_PASS', 'Yasin14@555');                     // change to your DB password
define('DB_CHARSET', 'utf8mb4');

// ── App ─────────────────────────────────────────────────────
define('APP_NAME',   'Media Converter');
define('APP_URL',    'https://mediaconverter.dezinegenius.com'); // no trailing slash
define('APP_EMAIL',  'no-reply@dezinegenius.com');

// ── Free plan limits ────────────────────────────────────────
define('FREE_CONVERSIONS_LIMIT', 3);  // conversions allowed per user total

// ── Session ─────────────────────────────────────────────────
define('SESSION_NAME', 'mc_session');

// ── Plans ───────────────────────────────────────────────────
define('PLANS', [
    'free'     => ['label' => 'Free',     'conversions' => 3,          'price' => 0],
    'pro'      => ['label' => 'Pro',      'conversions' => 'unlimited', 'price' => 9.99],
    'business' => ['label' => 'Business', 'conversions' => 'unlimited', 'price' => 24.99],
]);
