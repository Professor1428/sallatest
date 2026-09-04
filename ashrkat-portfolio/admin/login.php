<?php
require_once __DIR__ . '/../includes/bootstrap.php';
$pdo = db();

if (!has_any_admin($pdo)) {
    redirect('install.php');
}
if (is_logged_in()) {
    redirect('dashboard.php');
}

$error = null;
$ip = client_ip();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    if (too_many_attempts($pdo, $ip)) {
        $error = 'محاولات دخول كثيرة. حاولي مرة أخرى بعد 15 دقيقة.';
    } else {
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';

        if (attempt_login($pdo, $username, $password)) {
            clear_attempts($pdo, $ip);
            redirect('dashboard.php');
        }

        record_attempt($pdo, $ip);
        $error = 'اسم المستخدم أو كلمة المرور غير صحيحة.';
    }
}
?>
<!doctype html>
<html lang="ar" dir="rtl">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>تسجيل الدخول — لوحة التحكم</title>
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Cairo:wght@700;800&family=Tajawal:wght@400;500&display=swap">
<link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body class="admin-auth-body">
  <form class="admin-auth-card" method="post" novalidate>
    <div class="admin-auth-title">أشرقت كريم</div>
    <p class="admin-auth-hint">تسجيل الدخول للوحة التحكم</p>
    <?php if ($error): ?><div class="admin-flash admin-flash-error"><?= e($error) ?></div><?php endif; ?>
    <?= csrf_field() ?>
    <label>اسم المستخدم
      <input type="text" name="username" required autocomplete="username" autofocus>
    </label>
    <label>كلمة المرور
      <input type="password" name="password" required autocomplete="current-password">
    </label>
    <button type="submit">دخول</button>
  </form>
</body>
</html>
