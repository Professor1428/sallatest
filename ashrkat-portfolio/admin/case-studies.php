<?php
require_once __DIR__ . '/../includes/bootstrap.php';
require_login();
$pdo = db();
$pageTitle = 'دراسات الحالة';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $action = $_POST['action'] ?? '';

    try {
        if ($action === 'add') {
            $maxOrder = (int) $pdo->query('SELECT COALESCE(MAX(sort_order), -1) AS m FROM case_studies')->fetch()['m'];
            $imagePath = null;
            if (!empty($_FILES['image']['name'])) {
                $imagePath = handle_image_upload($_FILES['image'], 'case-studies');
            }
            $pdo->prepare('INSERT INTO case_studies (title, challenge, solution, result, image_path, sort_order) VALUES (?, ?, ?, ?, ?, ?)')
                ->execute([
                    trim($_POST['title'] ?? 'مشروع جديد'),
                    trim($_POST['challenge'] ?? ''),
                    trim($_POST['solution'] ?? ''),
                    trim($_POST['result'] ?? ''),
                    $imagePath,
                    $maxOrder + 1,
                ]);
            flash_set('success', 'تمت إضافة دراسة الحالة.');
        } elseif ($action === 'update') {
            $id = (int) ($_POST['id'] ?? 0);
            $stmt = $pdo->prepare('SELECT image_path FROM case_studies WHERE id = ?');
            $stmt->execute([$id]);
            $existing = $stmt->fetch();
            $imagePath = $existing['image_path'] ?? null;

            if (!empty($_FILES['image']['name'])) {
                $newPath = handle_image_upload($_FILES['image'], 'case-studies');
                if ($newPath) {
                    delete_uploaded_image($imagePath);
                    $imagePath = $newPath;
                }
            } elseif (!empty($_POST['remove_image'])) {
                delete_uploaded_image($imagePath);
                $imagePath = null;
            }

            $pdo->prepare('UPDATE case_studies SET title=?, challenge=?, solution=?, result=?, image_path=? WHERE id=?')
                ->execute([
                    trim($_POST['title'] ?? ''),
                    trim($_POST['challenge'] ?? ''),
                    trim($_POST['solution'] ?? ''),
                    trim($_POST['result'] ?? ''),
                    $imagePath,
                    $id,
                ]);
            flash_set('success', 'تم حفظ دراسة الحالة.');
        } elseif ($action === 'delete') {
            $id = (int) ($_POST['id'] ?? 0);
            $stmt = $pdo->prepare('SELECT image_path FROM case_studies WHERE id = ?');
            $stmt->execute([$id]);
            $existing = $stmt->fetch();
            if ($existing) {
                delete_uploaded_image($existing['image_path']);
            }
            $pdo->prepare('DELETE FROM case_studies WHERE id = ?')->execute([$id]);
            flash_set('success', 'تم حذف دراسة الحالة.');
        } elseif ($action === 'move') {
            reorder_move($pdo, 'case_studies', (int) ($_POST['id'] ?? 0), $_POST['direction'] === 'up' ? 'up' : 'down');
        }
    } catch (RuntimeException $e) {
        flash_set('error', $e->getMessage());
    }
    redirect('case-studies.php');
}

$caseStudies = $pdo->query('SELECT * FROM case_studies ORDER BY sort_order')->fetchAll();

require __DIR__ . '/includes/layout_top.php';
?>
<?php foreach ($caseStudies as $cs): ?>
  <form method="post" enctype="multipart/form-data" class="admin-card admin-form">
    <?= csrf_field() ?>
    <input type="hidden" name="action" value="update">
    <input type="hidden" name="id" value="<?= (int) $cs['id'] ?>">
    <label>اسم المشروع
      <input type="text" name="title" value="<?= e($cs['title']) ?>" required>
    </label>
    <label>التحدي
      <textarea name="challenge" rows="2"><?= e($cs['challenge']) ?></textarea>
    </label>
    <label>الحل
      <textarea name="solution" rows="2"><?= e($cs['solution']) ?></textarea>
    </label>
    <label>النتيجة
      <textarea name="result" rows="2"><?= e($cs['result']) ?></textarea>
    </label>
    <div class="admin-image-row">
      <?php if ($cs['image_path']): ?>
        <img src="../<?= e(UPLOAD_URL . '/' . $cs['image_path']) ?>" alt="" class="admin-thumb">
        <label class="admin-checkbox"><input type="checkbox" name="remove_image" value="1"> إزالة الصورة الحالية</label>
      <?php endif; ?>
      <label>استبدال / رفع صورة
        <input type="file" name="image" accept="image/jpeg,image/png,image/webp">
      </label>
    </div>
    <div class="admin-actions-row">
      <button type="submit" class="admin-btn-primary">حفظ</button>
    </div>
  </form>
  <div class="admin-inline-delete">
    <form method="post" class="admin-inline-form">
      <?= csrf_field() ?><input type="hidden" name="action" value="move"><input type="hidden" name="id" value="<?= (int) $cs['id'] ?>"><input type="hidden" name="direction" value="up">
      <button type="submit" class="admin-btn-plain">▲ نقل لأعلى</button>
    </form>
    <form method="post" class="admin-inline-form">
      <?= csrf_field() ?><input type="hidden" name="action" value="move"><input type="hidden" name="id" value="<?= (int) $cs['id'] ?>"><input type="hidden" name="direction" value="down">
      <button type="submit" class="admin-btn-plain">▼ نقل لأسفل</button>
    </form>
    <form method="post" onsubmit="return confirm('حذف دراسة الحالة هذه؟');" class="admin-inline-form">
      <?= csrf_field() ?><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= (int) $cs['id'] ?>">
      <button type="submit" class="admin-btn-danger-text">حذف</button>
    </form>
  </div>
<?php endforeach; ?>

<form method="post" enctype="multipart/form-data" class="admin-card admin-form">
  <?= csrf_field() ?>
  <input type="hidden" name="action" value="add">
  <h2>إضافة دراسة حالة جديدة</h2>
  <label>اسم المشروع
    <input type="text" name="title" required>
  </label>
  <label>التحدي
    <textarea name="challenge" rows="2"></textarea>
  </label>
  <label>الحل
    <textarea name="solution" rows="2"></textarea>
  </label>
  <label>النتيجة
    <textarea name="result" rows="2"></textarea>
  </label>
  <label>صورة المشروع
    <input type="file" name="image" accept="image/jpeg,image/png,image/webp">
  </label>
  <button type="submit" class="admin-btn-primary">إضافة</button>
</form>
<?php require __DIR__ . '/includes/layout_bottom.php'; ?>
