<?php
declare(strict_types=1);

function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="csrf_token" value="' . e(csrf_token()) . '">';
}

function verify_csrf(): void
{
    $sent = $_POST['csrf_token'] ?? '';
    $known = $_SESSION['csrf_token'] ?? '';
    if ($known === '' || !hash_equals($known, $sent)) {
        http_response_code(403);
        exit('طلب غير صالح (CSRF). حدّثي الصفحة وحاولي مرة أخرى.');
    }
}
