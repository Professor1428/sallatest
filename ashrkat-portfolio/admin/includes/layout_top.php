<?php
/** @var string $pageTitle */
$current = basename($_SERVER['SCRIPT_NAME']);
$unreadCount = (int) db()->query('SELECT COUNT(*) AS c FROM messages WHERE is_read = 0')->fetch()['c'];

$navItems = [
    'dashboard.php' => 'لوحة التحكم',
    'settings.php' => 'الإعدادات العامة',
    'skills.php' => 'المهارات',
    'packages.php' => 'الباقات والخدمات',
    'case-studies.php' => 'دراسات الحالة',
    'gallery.php' => 'معرض الأعمال',
    'testimonials.php' => 'آراء العملاء',
    'messages.php' => 'الرسائل الواردة',
    'change-password.php' => 'كلمة المرور',
];
?>
<!doctype html>
<html lang="ar" dir="rtl">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex, nofollow">
<title><?= e($pageTitle) ?> — لوحة التحكم</title>
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;800&family=Tajawal:wght@400;500;700&display=swap">
<link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body>
<div class="admin-shell">
  <aside class="admin-sidebar">
    <div class="admin-brand">
      <span class="admin-brand-mark">أ ك</span>
      <div>
        <div class="admin-brand-name">أشرقت كريم</div>
        <div class="admin-brand-sub">لوحة التحكم</div>
      </div>
    </div>
    <nav class="admin-nav">
      <?php foreach ($navItems as $href => $label): ?>
        <a href="<?= e($href) ?>" class="admin-nav-link <?= $current === $href ? 'is-active' : '' ?>">
          <?= e($label) ?>
          <?php if ($href === 'messages.php' && $unreadCount > 0): ?>
            <span class="admin-badge"><?= (int) $unreadCount ?></span>
          <?php endif; ?>
        </a>
      <?php endforeach; ?>
    </nav>
    <div class="admin-sidebar-footer">
      <a href="../index.php" target="_blank" class="admin-view-site">عرض الموقع ↗</a>
      <a href="logout.php" class="admin-logout">تسجيل الخروج</a>
    </div>
  </aside>
  <main class="admin-main">
    <header class="admin-topbar">
      <h1><?= e($pageTitle) ?></h1>
      <div class="admin-topbar-user">مرحبًا، <?= e($_SESSION['admin_username'] ?? '') ?></div>
    </header>
    <div class="admin-content">
      <?php $flash = flash_get(); ?>
      <?php if ($flash): ?>
        <div class="admin-flash admin-flash-<?= e($flash['type']) ?>"><?= e($flash['message']) ?></div>
      <?php endif; ?>
