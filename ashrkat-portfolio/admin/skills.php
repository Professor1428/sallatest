<?php
require_once __DIR__ . '/../includes/bootstrap.php';
require_login();
$pdo = db();
$pageTitle = 'المهارات';

$iconOptions = ['megaphone' => 'مكبر صوت', 'brush' => 'فرشاة', 'tools' => 'أدوات', 'camera' => 'كاميرا', 'chart' => 'رسم بياني', 'video' => 'فيديو'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $action = $_POST['action'] ?? '';

    if ($action === 'add_category') {
        $title = trim($_POST['title'] ?? '');
        $icon = $_POST['icon_key'] ?? 'megaphone';
        if ($title !== '') {
            $maxOrder = (int) $pdo->query('SELECT COALESCE(MAX(sort_order), -1) AS m FROM skill_categories')->fetch()['m'];
            $pdo->prepare('INSERT INTO skill_categories (title, icon_key, sort_order) VALUES (?, ?, ?)')
                ->execute([$title, in_array($icon, array_keys($iconOptions), true) ? $icon : 'megaphone', $maxOrder + 1]);
            flash_set('success', 'تمت إضافة التصنيف.');
        }
    } elseif ($action === 'update_category') {
        $id = (int) ($_POST['id'] ?? 0);
        $title = trim($_POST['title'] ?? '');
        $icon = $_POST['icon_key'] ?? 'megaphone';
        $items = nl_list($_POST['items'] ?? '');

        $pdo->prepare('UPDATE skill_categories SET title = ?, icon_key = ? WHERE id = ?')
            ->execute([$title, in_array($icon, array_keys($iconOptions), true) ? $icon : 'megaphone', $id]);

        $pdo->prepare('DELETE FROM skill_items WHERE category_id = ?')->execute([$id]);
        $stmt = $pdo->prepare('INSERT INTO skill_items (category_id, body, sort_order) VALUES (?, ?, ?)');
        foreach ($items as $i => $item) {
            $stmt->execute([$id, $item, $i]);
        }
        flash_set('success', 'تم تحديث التصنيف.');
    } elseif ($action === 'delete_category') {
        $id = (int) ($_POST['id'] ?? 0);
        $pdo->prepare('DELETE FROM skill_categories WHERE id = ?')->execute([$id]);
        flash_set('success', 'تم حذف التصنيف.');
    }
    redirect('skills.php');
}

$categories = $pdo->query('SELECT * FROM skill_categories ORDER BY sort_order')->fetchAll();
$itemsStmt = $pdo->prepare('SELECT body FROM skill_items WHERE category_id = ? ORDER BY sort_order');

require __DIR__ . '/includes/layout_top.php';
?>
<p class="admin-hint">كل تصنيف يظهر كعمود في صفحة "المهارات الأساسية" بالموقع. اكتبي كل مهارة في سطر مستقل.</p>

<?php foreach ($categories as $cat): ?>
  <?php $itemsStmt->execute([$cat['id']]); $items = array_column($itemsStmt->fetchAll(), 'body'); ?>
  <form method="post" class="admin-card admin-form">
    <?= csrf_field() ?>
    <input type="hidden" name="action" value="update_category">
    <input type="hidden" name="id" value="<?= (int) $cat['id'] ?>">
    <div class="admin-grid-2">
      <label>عنوان التصنيف
        <input type="text" name="title" value="<?= e($cat['title']) ?>" required>
      </label>
      <label>الأيقونة
        <select name="icon_key">
          <?php foreach ($iconOptions as $key => $label): ?>
            <option value="<?= e($key) ?>" <?= $cat['icon_key'] === $key ? 'selected' : '' ?>><?= e($label) ?></option>
          <?php endforeach; ?>
        </select>
      </label>
    </div>
    <label>المهارات (سطر لكل مهارة)
      <textarea name="items" rows="6"><?= e(implode("\n", $items)) ?></textarea>
    </label>
    <div class="admin-actions-row">
      <button type="submit" class="admin-btn-primary">حفظ التصنيف</button>
    </div>
  </form>
  <form method="post" onsubmit="return confirm('حذف هذا التصنيف بكل مهاراته؟');" class="admin-inline-delete">
    <?= csrf_field() ?>
    <input type="hidden" name="action" value="delete_category">
    <input type="hidden" name="id" value="<?= (int) $cat['id'] ?>">
    <button type="submit" class="admin-btn-danger-text">حذف تصنيف "<?= e($cat['title']) ?>"</button>
  </form>
<?php endforeach; ?>

<form method="post" class="admin-card admin-form">
  <?= csrf_field() ?>
  <input type="hidden" name="action" value="add_category">
  <h2>إضافة تصنيف جديد</h2>
  <div class="admin-grid-2">
    <label>عنوان التصنيف
      <input type="text" name="title" required>
    </label>
    <label>الأيقونة
      <select name="icon_key">
        <?php foreach ($iconOptions as $key => $label): ?>
          <option value="<?= e($key) ?>"><?= e($label) ?></option>
        <?php endforeach; ?>
      </select>
    </label>
  </div>
  <button type="submit" class="admin-btn-primary">إضافة</button>
</form>
<?php require __DIR__ . '/includes/layout_bottom.php'; ?>
