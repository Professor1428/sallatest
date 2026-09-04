<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/config.php';

date_default_timezone_set(SITE_TIMEZONE);
error_reporting(E_ALL);
ini_set('display_errors', '0'); // never leak errors/paths to visitors; check the server log instead

if (FORCE_HTTPS && empty($_SERVER['HTTPS'])) {
    $redirectUrl = 'https://' . ($_SERVER['HTTP_HOST'] ?? '') . ($_SERVER['REQUEST_URI'] ?? '/');
    header('Location: ' . $redirectUrl, true, 301);
    exit;
}

if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'domain' => '',
        'secure' => FORCE_HTTPS,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

// Baseline security headers for every response.
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('Referrer-Policy: strict-origin-when-cross-origin');
header('Permissions-Policy: camera=(), microphone=(), geolocation=()');

require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/csrf.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/auth.php';
