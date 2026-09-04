<?php
require_once __DIR__ . '/../includes/bootstrap.php';
require_login();
$pdo = db();
$pageTitle = 'الباقات والخدمات';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $action = $_POST['action'] ?? '';

    if ($action === 'add') {
        $maxOrder = (int) $pdo->query('SELECT COALESCE(MAX(sort_order), -1) AS m FROM packages')->fetch()['m'];
        $pdo->prepare('INSERT INTO packages (name, subtitle, audience, features, edge_text, is_highlight, sort_order) VALUES (?, ?, ?, ?, ?, ?, ?)')
            ->execute(['باقة جديدة', '', '', '', '', 0, $maxOrder + 1]);
        flash_set('success', 'تمت إضافة باقة جديدة، عدّلي بياناتها بالأسفل.');
    } elseif ($action === 'update') {
        $id = (int) ($_POST['id'] ?? 0);
        $pdo->prepare('UPDATE packages SET name=?, subtitle=?, audience=?, features=?, edge_text=?, is_highlight=? WHERE id=?')
            ->execute([
                trim($_POST['name'] ?? ''),
                trim($_POST['subtitle'] ?? ''),
                trim($_POST['audience'] ?? ''),
                trim($_POST['features'] ?? ''),
                trim($_POST['edge_text'] ?? ''),
                isset($_POST['is_highlight']) ? 1 : 0,
                $id,
            ]);
        flash_set('success', 'تم حفظ الباقة.');
    } elseif ($action === 'delete') {
        $pdo->prepare('DELETE FROM packages WHERE id = ?')->execute([(int) ($_POST['id'] ?? 0)]);
        flash_set('success', 'تم حذف الباقة.');
    } elseif ($action === 'move') {
        reorder_move($pdo, 'packages', (int) ($_POST['id'] ?? 0), $_POST['direction'] === 'up' ? 'up' : 'down');
    }
    redirect('packages.php');
}

$packages = $pdo->query('SELECT * FROM packages ORDER BY sort_order')->fetchAll();

require __DIR__ . '/includes/layout_top.php';
?>
<p class="admin-hint">اكتبي كل مخرج/ميزة في الباقة في سطر مستقل داخل حقل "المخرجات".</p>

<?php foreach ($packages as $pkg): ?>
  <form method="post" class="admin-card admin-form">
    <?= csrf_field() ?>
    <input type="hidden" name="action" value="update">
    <input type="hidden" name="id" value="<?= (int) $pkg['id'] ?>">
    <div class="admin-grid-2">
      <label>اسم الباقة
        <input type="text" name="name" value="<?= e($pkg['name']) ?>" required>
      </label>
      <label>الاسم الفرعي (إنجليزي)
        <input type="text" name="subtitle" value="<?= e($pkg['subtitle']) ?>">
      </label>
    </div>
    <label>لمن توجّه الباقة
      <input type="text" name="audience" value="<?= e($pkg['audience']) ?>">
    </label>
    <label>المخرجات (سطر لكل بند)
      <textarea name="features" rows="5"><?= e($pkg['features']) ?></textarea>
    </label>
    <label>الميزة التنافسية
      <textarea name="edge_text" rows="2"><?= e($pkg['edge_text']) ?></textarea>
    </label>
    <label class="admin-checkbox">
      <input type="checkbox" name="is_highlight" <?= $pkg['is_highlight'] ? 'checked' : '' ?>>
      إبراز هذه الباقة كـ"الأكثر طلبًا"
    </label>
    <div class="admin-actions-row">
      <button type="submit" class="admin-btn-primary">حفظ</button>
    </div>
  </form>
  <div class="admin-inline-delete">
    <form method="post" class="admin-inline-form">
      <?= csrf_field() ?><input type="hidden" name="action" value="move"><input type="hidden" name="id" value="<?= (int) $pkg['id'] ?>"><input type="hidden" name="direction" value="up">
      <button type="submit" class="admin-btn-plain">▲ نقل لأعلى</button>
    </form>
    <form method="post" class="admin-inline-form">
      <?= csrf_field() ?><input type="hidden" name="action" value="move"><input type="hidden" name="id" value="<?= (int) $pkg['id'] ?>"><input type="hidden" name="direction" value="down">
      <button type="submit" class="admin-btn-plain">▼ نقل لأسفل</button>
    </form>
    <form method="post" onsubmit="return confirm('حذف هذه الباقة؟');" class="admin-inline-form">
      <?= csrf_field() ?><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= (int) $pkg['id'] ?>">
      <button type="submit" class="admin-btn-danger-text">حذف الباقة</button>
    </form>
  </div>
<?php endforeach; ?>

<form method="post" class="admin-card">
  <?= csrf_field() ?>
  <input type="hidden" name="action" value="add">
  <button type="submit" class="admin-btn-primary">+ إضافة باقة جديدة</button>
</form>
<?php require __DIR__ . '/includes/layout_bottom.php'; ?>
