<?php
// auth/logout.php
require_once __DIR__ . '/../includes/auth.php';
logout_user();
header('Location: ' . APP_URL . '/auth/login.php');
exit;
