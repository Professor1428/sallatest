<?php
require_once __DIR__ . '/../includes/bootstrap.php';
require_login();
$pdo = db();
$pageTitle = 'آراء العملاء';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $action = $_POST['action'] ?? '';

    if ($action === 'add') {
        $maxOrder = (int) $pdo->query('SELECT COALESCE(MAX(sort_order), -1) AS m FROM testimonials')->fetch()['m'];
        $pdo->prepare('INSERT INTO testimonials (quote, author, rating, sort_order) VALUES (?, ?, ?, ?)')
            ->execute([
                trim($_POST['quote'] ?? ''),
                trim($_POST['author'] ?? ''),
                max(1, min(5, (int) ($_POST['rating'] ?? 5))),
                $maxOrder + 1,
            ]);
        flash_set('success', 'تمت إضافة الرأي.');
    } elseif ($action === 'update') {
        $id = (int) ($_POST['id'] ?? 0);
        $pdo->prepare('UPDATE testimonials SET quote=?, author=?, rating=? WHERE id=?')
            ->execute([
                trim($_POST['quote'] ?? ''),
                trim($_POST['author'] ?? ''),
                max(1, min(5, (int) ($_POST['rating'] ?? 5))),
                $id,
            ]);
        flash_set('success', 'تم حفظ الرأي.');
    } elseif ($action === 'delete') {
        $pdo->prepare('DELETE FROM testimonials WHERE id = ?')->execute([(int) ($_POST['id'] ?? 0)]);
        flash_set('success', 'تم الحذف.');
    } elseif ($action === 'move') {
        reorder_move($pdo, 'testimonials', (int) ($_POST['id'] ?? 0), $_POST['direction'] === 'up' ? 'up' : 'down');
    }
    redirect('testimonials.php');
}

$testimonials = $pdo->query('SELECT * FROM testimonials ORDER BY sort_order')->fetchAll();

require __DIR__ . '/includes/layout_top.php';
?>
<?php foreach ($testimonials as $t): ?>
  <form method="post" class="admin-card admin-form">
    <?= csrf_field() ?>
    <input type="hidden" name="action" value="update">
    <input type="hidden" name="id" value="<?= (int) $t['id'] ?>">
    <label>نص الرأي
      <textarea name="quote" rows="3" required><?= e($t['quote']) ?></textarea>
    </label>
    <div class="admin-grid-2">
      <label>الاسم / الصفة
        <input type="text" name="author" value="<?= e($t['author']) ?>">
      </label>
      <label>التقييم (1-5)
        <input type="number" name="rating" min="1" max="5" value="<?= (int) $t['rating'] ?>">
      </label>
    </div>
    <div class="admin-actions-row">
      <button type="submit" class="admin-btn-primary">حفظ</button>
    </div>
  </form>
  <div class="admin-inline-delete">
    <form method="post" class="admin-inline-form">
      <?= csrf_field() ?><input type="hidden" name="action" value="move"><input type="hidden" name="id" value="<?= (int) $t['id'] ?>"><input type="hidden" name="direction" value="up">
      <button type="submit" class="admin-btn-plain">▲ نقل لأعلى</button>
    </form>
    <form method="post" class="admin-inline-form">
      <?= csrf_field() ?><input type="hidden" name="action" value="move"><input type="hidden" name="id" value="<?= (int) $t['id'] ?>"><input type="hidden" name="direction" value="down">
      <button type="submit" class="admin-btn-plain">▼ نقل لأسفل</button>
    </form>
    <form method="post" onsubmit="return confirm('حذف هذا الرأي؟');" class="admin-inline-form">
      <?= csrf_field() ?><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= (int) $t['id'] ?>">
      <button type="submit" class="admin-btn-danger-text">حذف</button>
    </form>
  </div>
<?php endforeach; ?>

<form method="post" class="admin-card admin-form">
  <?= csrf_field() ?>
  <input type="hidden" name="action" value="add">
  <h2>إضافة رأي جديد</h2>
  <label>نص الرأي
    <textarea name="quote" rows="3" required></textarea>
  </label>
  <div class="admin-grid-2">
    <label>الاسم / الصفة
      <input type="text" name="author" placeholder="مثال: صاحب مطعم">
    </label>
    <label>التقييم (1-5)
      <input type="number" name="rating" min="1" max="5" value="5">
    </label>
  </div>
  <button type="submit" class="admin-btn-primary">إضافة</button>
</form>
<?php require __DIR__ . '/includes/layout_bottom.php'; ?>
