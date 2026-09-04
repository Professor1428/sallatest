<?php
require_once __DIR__ . '/../includes/bootstrap.php';
require_login();
$pdo = db();
$pageTitle = 'الرسائل الواردة';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $action = $_POST['action'] ?? '';
    $id = (int) ($_POST['id'] ?? 0);

    if ($action === 'mark_read') {
        $pdo->prepare('UPDATE messages SET is_read = 1 WHERE id = ?')->execute([$id]);
    } elseif ($action === 'mark_unread') {
        $pdo->prepare('UPDATE messages SET is_read = 0 WHERE id = ?')->execute([$id]);
    } elseif ($action === 'delete') {
        $pdo->prepare('DELETE FROM messages WHERE id = ?')->execute([$id]);
        flash_set('success', 'تم حذف الرسالة.');
    }
    redirect('messages.php');
}

$messages = $pdo->query('SELECT * FROM messages ORDER BY created_at DESC')->fetchAll();

require __DIR__ . '/includes/layout_top.php';
?>
<?php if (!$messages): ?>
  <div class="admin-card"><p class="admin-hint">لا توجد رسائل حتى الآن.</p></div>
<?php endif; ?>

<?php foreach ($messages as $m): ?>
  <div class="admin-card admin-message <?= $m['is_read'] ? '' : 'is-unread' ?>">
    <div class="admin-message-head">
      <div>
        <strong><?= e($m['name']) ?></strong>
        <span class="admin-hint"> — <?= e($m['created_at']) ?></span>
      </div>
      <?php if (!$m['is_read']): ?><span class="admin-badge">جديدة</span><?php endif; ?>
    </div>
    <div class="admin-message-contact">
      <a href="mailto:<?= e($m['email']) ?>"><?= e($m['email']) ?></a>
      <?php if ($m['phone']): ?> · <span dir="ltr"><?= e($m['phone']) ?></span><?php endif; ?>
    </div>
    <p class="admin-message-body"><?= nl2br(e($m['body'])) ?></p>
    <div class="admin-inline-delete">
      <form method="post" class="admin-inline-form">
        <?= csrf_field() ?>
        <input type="hidden" name="id" value="<?= (int) $m['id'] ?>">
        <input type="hidden" name="action" value="<?= $m['is_read'] ? 'mark_unread' : 'mark_read' ?>">
        <button type="submit" class="admin-btn-plain"><?= $m['is_read'] ? 'تمييز كغير مقروءة' : 'تمييز كمقروءة' ?></button>
      </form>
      <form method="post" onsubmit="return confirm('حذف هذه الرسالة؟');" class="admin-inline-form">
        <?= csrf_field() ?>
        <input type="hidden" name="id" value="<?= (int) $m['id'] ?>">
        <input type="hidden" name="action" value="delete">
        <button type="submit" class="admin-btn-danger-text">حذف</button>
      </form>
    </div>
  </div>
<?php endforeach; ?>
<?php require __DIR__ . '/includes/layout_bottom.php'; ?>
