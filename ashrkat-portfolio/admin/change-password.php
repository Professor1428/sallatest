<?php
require_once __DIR__ . '/../includes/bootstrap.php';
require_login();
$pdo = db();
$pageTitle = 'كلمة المرور';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $current = $_POST['current_password'] ?? '';
    $new = $_POST['new_password'] ?? '';
    $confirm = $_POST['new_password_confirm'] ?? '';

    $stmt = $pdo->prepare('SELECT password_hash FROM admins WHERE id = ?');
    $stmt->execute([$_SESSION['admin_id']]);
    $admin = $stmt->fetch();

    if (!$admin || !password_verify($current, $admin['password_hash'])) {
        flash_set('error', 'كلمة المرور الحالية غير صحيحة.');
    } elseif (mb_strlen($new) < 8) {
        flash_set('error', 'كلمة المرور الجديدة يجب أن تكون 8 أحرف على الأقل.');
    } elseif ($new !== $confirm) {
        flash_set('error', 'كلمتا المرور الجديدتان غير متطابقتين.');
    } else {
        $pdo->prepare('UPDATE admins SET password_hash = ? WHERE id = ?')
            ->execute([password_hash($new, PASSWORD_DEFAULT), $_SESSION['admin_id']]);
        flash_set('success', 'تم تغيير كلمة المرور بنجاح.');
    }
    redirect('change-password.php');
}

require __DIR__ . '/includes/layout_top.php';
?>
<form method="post" class="admin-card admin-form" style="max-width:480px">
  <?= csrf_field() ?>
  <label>كلمة المرور الحالية
    <input type="password" name="current_password" required autocomplete="current-password">
  </label>
  <label>كلمة المرور الجديدة
    <input type="password" name="new_password" required minlength="8" autocomplete="new-password">
  </label>
  <label>تأكيد كلمة المرور الجديدة
    <input type="password" name="new_password_confirm" required minlength="8" autocomplete="new-password">
  </label>
  <button type="submit" class="admin-btn-primary">تحديث كلمة المرور</button>
</form>
<?php require __DIR__ . '/includes/layout_bottom.php'; ?>
