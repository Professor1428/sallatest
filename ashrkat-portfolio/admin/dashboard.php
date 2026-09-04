<?php
require_once __DIR__ . '/../includes/bootstrap.php';
require_login();
$pdo = db();
$pageTitle = 'لوحة التحكم';

$stats = [
    'الرسائل غير المقروءة' => (int) $pdo->query('SELECT COUNT(*) AS c FROM messages WHERE is_read = 0')->fetch()['c'],
    'صور معرض الأعمال' => (int) $pdo->query('SELECT COUNT(*) AS c FROM gallery')->fetch()['c'],
    'دراسات الحالة' => (int) $pdo->query('SELECT COUNT(*) AS c FROM case_studies')->fetch()['c'],
    'الباقات المنشورة' => (int) $pdo->query('SELECT COUNT(*) AS c FROM packages')->fetch()['c'],
    'آراء العملاء' => (int) $pdo->query('SELECT COUNT(*) AS c FROM testimonials')->fetch()['c'],
];

require __DIR__ . '/includes/layout_top.php';
?>
<div class="admin-stat-grid">
  <?php foreach ($stats as $label => $value): ?>
    <div class="admin-stat-card">
      <div class="admin-stat-value"><?= (int) $value ?></div>
      <div class="admin-stat-label"><?= e($label) ?></div>
    </div>
  <?php endforeach; ?>
</div>

<div class="admin-card">
  <h2>روابط سريعة</h2>
  <div class="admin-quicklinks">
    <a href="gallery.php" class="admin-quicklink">+ إضافة صور لمعرض الأعمال</a>
    <a href="case-studies.php" class="admin-quicklink">+ إضافة دراسة حالة</a>
    <a href="testimonials.php" class="admin-quicklink">+ إضافة رأي عميل</a>
    <a href="settings.php" class="admin-quicklink">تعديل بيانات الموقع والألوان</a>
    <a href="messages.php" class="admin-quicklink">مراجعة الرسائل الواردة</a>
  </div>
</div>

<?php require __DIR__ . '/includes/layout_bottom.php'; ?>
