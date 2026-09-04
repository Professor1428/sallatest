<?php
require_once __DIR__ . '/../includes/bootstrap.php';
$pdo = db();

if (has_any_admin($pdo)) {
    redirect('login.php');
}

$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $setupKey = trim($_POST['setup_key'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['password_confirm'] ?? '';

    if (!hash_equals(SETUP_KEY, $setupKey)) {
        $error = 'مفتاح الإعداد غير صحيح.';
    } elseif (mb_strlen($username) < 3) {
        $error = 'اسم المستخدم يجب أن يكون 3 أحرف على الأقل.';
    } elseif (mb_strlen($password) < 8) {
        $error = 'كلمة المرور يجب أن تكون 8 أحرف على الأقل.';
    } elseif ($password !== $confirm) {
        $error = 'كلمتا المرور غير متطابقتين.';
    } else {
        // Re-check right before insert to close the race window between two simultaneous installs.
        if (has_any_admin($pdo)) {
            redirect('login.php');
        }
        $stmt = $pdo->prepare('INSERT INTO admins (username, password_hash) VALUES (?, ?)');
        $stmt->execute([$username, password_hash($password, PASSWORD_DEFAULT)]);
        flash_set('success', 'تم إنشاء الحساب بنجاح. سجّلي الدخول للمتابعة.');
        redirect('login.php');
    }
}
?>
<!doctype html>
<html lang="ar" dir="rtl">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>إعداد الحساب الأول</title>
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Cairo:wght@700;800&family=Tajawal:wght@400;500&display=swap">
<link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body class="admin-auth-body">
  <form class="admin-auth-card" method="post" novalidate>
    <div class="admin-auth-title">إنشاء حساب الإدارة الأول</div>
    <p class="admin-auth-hint">هذه الصفحة تعمل مرة واحدة فقط لإنشاء أول حساب دخول للوحة التحكم.</p>
    <?php if ($error): ?><div class="admin-flash admin-flash-error"><?= e($error) ?></div><?php endif; ?>
    <?= csrf_field() ?>
    <label>مفتاح الإعداد (SETUP_KEY من config.php)
      <input type="text" name="setup_key" required autocomplete="off">
    </label>
    <label>اسم المستخدم
      <input type="text" name="username" required minlength="3" autocomplete="off">
    </label>
    <label>كلمة المرور
      <input type="password" name="password" required minlength="8" autocomplete="new-password">
    </label>
    <label>تأكيد كلمة المرور
      <input type="password" name="password_confirm" required minlength="8" autocomplete="new-password">
    </label>
    <button type="submit">إنشاء الحساب</button>
  </form>
</body>
</html>
