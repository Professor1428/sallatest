<?php
declare(strict_types=1);

function is_logged_in(): bool
{
    return !empty($_SESSION['admin_id']);
}

function require_login(): void
{
    if (!is_logged_in()) {
        redirect('login.php');
    }
}

function has_any_admin(PDO $pdo): bool
{
    return (int) $pdo->query('SELECT COUNT(*) AS c FROM admins')->fetch()['c'] > 0;
}

function attempt_login(PDO $pdo, string $username, string $password): bool
{
    $stmt = $pdo->prepare('SELECT id, password_hash FROM admins WHERE username = ?');
    $stmt->execute([$username]);
    $admin = $stmt->fetch();

    if (!$admin || !password_verify($password, $admin['password_hash'])) {
        return false;
    }

    session_regenerate_id(true);
    $_SESSION['admin_id'] = (int) $admin['id'];
    $_SESSION['admin_username'] = $username;
    return true;
}

function logout(): void
{
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
    }
    session_destroy();
}
