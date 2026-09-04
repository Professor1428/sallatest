<?php
require_once __DIR__ . '/../includes/bootstrap.php';
require_login();
$pdo = db();
$pageTitle = 'معرض الأعمال';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $action = $_POST['action'] ?? '';

    try {
        if ($action === 'add') {
            if (empty($_FILES['image']['name'])) {
                flash_set('error', 'اختاري صورة أولًا.');
            } else {
                $path = handle_image_upload($_FILES['image'], 'gallery');
                $maxOrder = (int) $pdo->query('SELECT COALESCE(MAX(sort_order), -1) AS m FROM gallery')->fetch()['m'];
                $pdo->prepare('INSERT INTO gallery (image_path, caption, sort_order) VALUES (?, ?, ?)')
                    ->execute([$path, trim($_POST['caption'] ?? ''), $maxOrder + 1]);
                flash_set('success', 'تمت إضافة الصورة إلى المعرض.');
            }
        } elseif ($action === 'update_caption') {
            $id = (int) ($_POST['id'] ?? 0);
            $pdo->prepare('UPDATE gallery SET caption = ? WHERE id = ?')->execute([trim($_POST['caption'] ?? ''), $id]);
            flash_set('success', 'تم تحديث الوصف.');
        } elseif ($action === 'delete') {
            $id = (int) ($_POST['id'] ?? 0);
            $stmt = $pdo->prepare('SELECT image_path FROM gallery WHERE id = ?');
            $stmt->execute([$id]);
            $row = $stmt->fetch();
            if ($row) {
                delete_uploaded_image($row['image_path']);
            }
            $pdo->prepare('DELETE FROM gallery WHERE id = ?')->execute([$id]);
            flash_set('success', 'تم حذف الصورة.');
        } elseif ($action === 'move') {
            reorder_move($pdo, 'gallery', (int) ($_POST['id'] ?? 0), $_POST['direction'] === 'up' ? 'up' : 'down');
        }
    } catch (RuntimeException $e) {
        flash_set('error', $e->getMessage());
    }
    redirect('gallery.php');
}

$images = $pdo->query('SELECT * FROM gallery ORDER BY sort_order')->fetchAll();

require __DIR__ . '/includes/layout_top.php';
?>
<form method="post" enctype="multipart/form-data" class="admin-card admin-form">
  <?= csrf_field() ?>
  <input type="hidden" name="action" value="add">
  <h2>إضافة صورة جديدة</h2>
  <div class="admin-grid-2">
    <label>الصورة
      <input type="file" name="image" accept="image/jpeg,image/png,image/webp" required>
    </label>
    <label>وصف مختصر (اختياري)
      <input type="text" name="caption">
    </label>
  </div>
  <button type="submit" class="admin-btn-primary">رفع الصورة</button>
</form>

<div class="admin-gallery-grid">
  <?php foreach ($images as $img): ?>
    <div class="admin-gallery-item">
      <img src="../<?= e(UPLOAD_URL . '/' . $img['image_path']) ?>" alt="">
      <form method="post" class="admin-gallery-caption">
        <?= csrf_field() ?>
        <input type="hidden" name="action" value="update_caption">
        <input type="hidden" name="id" value="<?= (int) $img['id'] ?>">
        <input type="text" name="caption" value="<?= e($img['caption']) ?>" placeholder="وصف الصورة">
        <button type="submit" class="admin-btn-plain">حفظ</button>
      </form>
      <div class="admin-inline-delete">
        <form method="post" class="admin-inline-form">
          <?= csrf_field() ?><input type="hidden" name="action" value="move"><input type="hidden" name="id" value="<?= (int) $img['id'] ?>"><input type="hidden" name="direction" value="up">
          <button type="submit" class="admin-btn-plain">▲</button>
        </form>
        <form method="post" class="admin-inline-form">
          <?= csrf_field() ?><input type="hidden" name="action" value="move"><input type="hidden" name="id" value="<?= (int) $img['id'] ?>"><input type="hidden" name="direction" value="down">
          <button type="submit" class="admin-btn-plain">▼</button>
        </form>
        <form method="post" onsubmit="return confirm('حذف هذه الصورة؟');" class="admin-inline-form">
          <?= csrf_field() ?><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= (int) $img['id'] ?>">
          <button type="submit" class="admin-btn-danger-text">حذف</button>
        </form>
      </div>
    </div>
  <?php endforeach; ?>
  <?php if (!$images): ?><p class="admin-hint">لا توجد صور بعد.</p><?php endif; ?>
</div>
<?php require __DIR__ . '/includes/layout_bottom.php'; ?>
