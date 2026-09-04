<?php
require_once __DIR__ . '/../includes/bootstrap.php';
require_login();
$pdo = db();
$pageTitle = 'الإعدادات العامة';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    $fields = [
        'site_name', 'role_title', 'tagline',
        'about_kicker', 'about_text',
        'vision_kicker', 'vision_text',
        'cta_heading', 'cta_text',
        'email', 'phone', 'footer_note',
        'color_dark', 'color_light', 'color_gold', 'color_emerald', 'color_ink',
    ];
    $values = [];
    foreach ($fields as $field) {
        $values[$field] = trim($_POST[$field] ?? '');
    }

    if ($values['email'] !== '' && !filter_var($values['email'], FILTER_VALIDATE_EMAIL)) {
        flash_set('error', 'صيغة البريد الإلكتروني غير صحيحة.');
    } else {
        $sql = 'UPDATE settings SET ' . implode(', ', array_map(fn($f) => "$f = :$f", $fields)) . ' WHERE id = 1';
        $stmt = $pdo->prepare($sql);
        $stmt->execute($values);
        flash_set('success', 'تم حفظ الإعدادات بنجاح.');
    }
    redirect('settings.php');
}

$settings = $pdo->query('SELECT * FROM settings WHERE id = 1')->fetch();

require __DIR__ . '/includes/layout_top.php';
?>
<form method="post" class="admin-card admin-form">
  <?= csrf_field() ?>

  <h2>بيانات الهوية</h2>
  <div class="admin-grid-2">
    <label>اسم الموقع
      <input type="text" name="site_name" value="<?= e($settings['site_name']) ?>" required>
    </label>
    <label>المسمى الوظيفي
      <input type="text" name="role_title" value="<?= e($settings['role_title']) ?>" required>
    </label>
  </div>
  <label>الشعار / التاجلاين (صفحة الغلاف)
    <input type="text" name="tagline" value="<?= e($settings['tagline']) ?>">
  </label>

  <h2>نبذة عني</h2>
  <label>عنوان فرعي
    <input type="text" name="about_kicker" value="<?= e($settings['about_kicker']) ?>">
  </label>
  <label>نص النبذة
    <textarea name="about_text" rows="5"><?= e($settings['about_text']) ?></textarea>
  </label>

  <h2>رؤيتي في العمل</h2>
  <label>عنوان فرعي
    <input type="text" name="vision_kicker" value="<?= e($settings['vision_kicker']) ?>">
  </label>
  <label>نص الرؤية
    <textarea name="vision_text" rows="5"><?= e($settings['vision_text']) ?></textarea>
  </label>

  <h2>قسم التواصل</h2>
  <label>عنوان قسم التواصل
    <input type="text" name="cta_heading" value="<?= e($settings['cta_heading']) ?>">
  </label>
  <label>نص الدعوة للتواصل
    <textarea name="cta_text" rows="3"><?= e($settings['cta_text']) ?></textarea>
  </label>
  <div class="admin-grid-2">
    <label>البريد الإلكتروني
      <input type="email" name="email" value="<?= e($settings['email']) ?>" required>
    </label>
    <label>رقم الهاتف
      <input type="text" name="phone" value="<?= e($settings['phone']) ?>" required>
    </label>
  </div>
  <label>نص الفوتر
    <input type="text" name="footer_note" value="<?= e($settings['footer_note']) ?>">
  </label>

  <h2>ألوان التصميم</h2>
  <p class="admin-hint">هذه الألوان تُطبَّق مباشرة على الموقع بالكامل.</p>
  <div class="admin-grid-4">
    <label>الخلفية الداكنة
      <input type="color" name="color_dark" value="<?= e($settings['color_dark']) ?>">
    </label>
    <label>الخلفية الفاتحة
      <input type="color" name="color_light" value="<?= e($settings['color_light']) ?>">
    </label>
    <label>الذهبي (Gold)
      <input type="color" name="color_gold" value="<?= e($settings['color_gold']) ?>">
    </label>
    <label>الزمردي (Emerald)
      <input type="color" name="color_emerald" value="<?= e($settings['color_emerald']) ?>">
    </label>
  </div>
  <label>لون النص الأساسي
    <input type="color" name="color_ink" value="<?= e($settings['color_ink']) ?>">
  </label>

  <button type="submit" class="admin-btn-primary">حفظ التغييرات</button>
</form>
<?php require __DIR__ . '/includes/layout_bottom.php'; ?>
