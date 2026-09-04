<?php
require_once __DIR__ . '/includes/bootstrap.php';
$pdo = db();

$settings = $pdo->query('SELECT * FROM settings WHERE id = 1')->fetch();
$categories = $pdo->query('SELECT * FROM skill_categories ORDER BY sort_order')->fetchAll();
$itemsStmt = $pdo->prepare('SELECT body FROM skill_items WHERE category_id = ? ORDER BY sort_order');
$packages = $pdo->query('SELECT * FROM packages ORDER BY sort_order')->fetchAll();
$gallery = $pdo->query('SELECT * FROM gallery ORDER BY sort_order')->fetchAll();
$caseStudies = $pdo->query('SELECT * FROM case_studies ORDER BY sort_order')->fetchAll();
$testimonials = $pdo->query('SELECT * FROM testimonials ORDER BY sort_order')->fetchAll();

$flash = flash_get();
$year = date('Y');
?>
<!doctype html>
<html lang="ar" dir="rtl">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($settings['site_name']) ?> — <?= e($settings['role_title']) ?></title>
<meta name="description" content="<?= e($settings['tagline']) ?>">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;800;900&family=Tajawal:wght@300;400;500;700&display=swap">
<link rel="stylesheet" href="assets/css/style.css">
<style>
  :root {
    --dark: <?= e($settings['color_dark']) ?>;
    --light: <?= e($settings['color_light']) ?>;
    --gold: <?= e($settings['color_gold']) ?>;
    --emerald: <?= e($settings['color_emerald']) ?>;
    --ink: <?= e($settings['color_ink']) ?>;
  }
</style>
</head>
<body>

<div class="preloader"><div class="preloader-mark">ASHRKAT KAREEM</div></div>
<div class="scroll-progress"></div>

<nav class="navbar">
  <div class="container">
    <a href="#home" class="nav-logo"><?= e($settings['site_name']) ?><span>.</span></a>
    <button class="nav-toggle" aria-label="القائمة"><span></span><span></span><span></span></button>
    <ul class="nav-links">
      <li><a href="#home">الرئيسية</a></li>
      <li><a href="#about">نبذة عني</a></li>
      <li><a href="#skills">المهارات</a></li>
      <li><a href="#services">الباقات</a></li>
      <li><a href="#gallery">معرض الأعمال</a></li>
      <li><a href="#work">دراسات الحالة</a></li>
      <li><a href="#testimonials">آراء العملاء</a></li>
      <li><a href="#contact" class="nav-cta">تواصل معي</a></li>
    </ul>
  </div>
</nav>

<section class="hero" id="home">
  <div class="hero-noise"></div>
  <div class="hero-ring hero-ring--1"></div>
  <div class="hero-ring hero-ring--2"></div>
  <div class="hero-orb hero-orb--gold"></div>
  <div class="hero-orb hero-orb--emerald"></div>
  <div class="container hero-inner">
    <div class="hero-kicker" data-reveal>ملف أعمال احترافي</div>
    <h1 class="hero-title" data-reveal><?= e($settings['site_name']) ?></h1>
    <div class="hero-role" data-reveal><?= e($settings['role_title']) ?></div>
    <p class="hero-tagline" data-reveal><?= e($settings['tagline']) ?></p>
    <div class="hero-actions" data-reveal>
      <a href="#contact" class="btn btn-primary">لنبدأ التعاون</a>
      <a href="#gallery" class="btn btn-ghost">شاهدي أعمالي</a>
    </div>
  </div>
  <div class="scroll-cue"><span class="scroll-cue-dot"></span>SCROLL</div>
</section>

<section class="section section-light" id="about">
  <div class="container about-grid">
    <div>
      <div class="about-block" data-reveal>
        <div class="kicker"><?= e($settings['about_kicker']) ?></div>
        <h2 class="section-title">نبذة عني</h2>
        <p class="about-text"><?= nl2br(e($settings['about_text'])) ?></p>
      </div>
      <div class="about-block" data-reveal>
        <div class="kicker"><?= e($settings['vision_kicker']) ?></div>
        <p class="about-text"><?= nl2br(e($settings['vision_text'])) ?></p>
      </div>
    </div>
    <div class="about-card" data-reveal-scale>
      <div class="about-card-name"><?= e($settings['site_name']) ?></div>
      <div class="about-card-role"><?= e($settings['role_title']) ?></div>
      <div class="about-contact">
        <a href="mailto:<?= e($settings['email']) ?>">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="m4 7 8 6 8-6"/></svg>
          <span dir="ltr"><?= e($settings['email']) ?></span>
        </a>
        <a href="tel:<?= e($settings['phone']) ?>">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><path d="M4 5c0 8.3 6.7 15 15 15l3-3.3c.3-.3.3-.9-.1-1.2l-4-3a1 1 0 0 0-1.2.1l-1.7 1.7a12 12 0 0 1-5.3-5.3l1.7-1.7c.3-.3.4-.9.1-1.2l-3-4A1 1 0 0 0 7.3 2L4 5z"/></svg>
          <span dir="ltr"><?= e($settings['phone']) ?></span>
        </a>
      </div>
    </div>
  </div>
</section>

<section class="section section-light" id="skills" style="padding-top:0">
  <div class="container">
    <div class="kicker" data-reveal>الخبرات والأدوات</div>
    <h2 class="section-title" data-reveal>المهارات الأساسية</h2>
    <p class="section-lead" data-reveal>مزيج متكامل بين التفكير التسويقي والتنفيذ الإبداعي.</p>
    <div class="skills-grid">
      <?php foreach ($categories as $cat): ?>
        <?php $itemsStmt->execute([$cat['id']]); $items = $itemsStmt->fetchAll(); ?>
        <div class="tilt-card" data-reveal data-reveal-group="skills">
          <div class="skill-card">
            <div class="skill-icon"><?= icon_svg($cat['icon_key'], 30) ?></div>
            <div class="skill-rule"></div>
            <h3><?= e($cat['title']) ?></h3>
            <ul class="skill-list">
              <?php foreach ($items as $item): ?>
                <li>
                  <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg>
                  <span><?= e($item['body']) ?></span>
                </li>
              <?php endforeach; ?>
            </ul>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<section class="section section-light" id="services" style="padding-top:0">
  <div class="container">
    <div class="kicker" data-reveal>التعاون معي</div>
    <h2 class="section-title" data-reveal>الخدمات والباقات</h2>
    <p class="section-lead" data-reveal>باقات شهرية متكاملة تجمع بين التسويق والتصميم، مصممة لأصحاب الكافيهات والمطاعم والمتاجر الإلكترونية.</p>
    <div class="packages-grid">
      <?php foreach ($packages as $pkg): ?>
        <?php $features = nl_list($pkg['features']); ?>
        <div class="package-card <?= $pkg['is_highlight'] ? 'is-highlight' : '' ?>" data-reveal data-reveal-group="packages">
          <?php if ($pkg['is_highlight']): ?><div class="package-badge">الأكثر طلبًا</div><?php endif; ?>
          <div>
            <div class="package-name"><?= e($pkg['name']) ?></div>
            <div class="package-subtitle"><?= e($pkg['subtitle']) ?></div>
          </div>
          <p class="package-audience">لمن: <?= e($pkg['audience']) ?></p>
          <ul class="package-features">
            <?php foreach ($features as $f): ?>
              <li>
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg>
                <span><?= e($f) ?></span>
              </li>
            <?php endforeach; ?>
          </ul>
          <?php if ($pkg['edge_text']): ?>
            <div class="package-edge"><strong>الميزة التنافسية:</strong> <?= e($pkg['edge_text']) ?></div>
          <?php endif; ?>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<?php if ($gallery): ?>
<section class="section section-light" id="gallery" style="padding-top:0">
  <div class="container">
    <div class="kicker" data-reveal>أعمال مختارة</div>
    <h2 class="section-title" data-reveal>معرض الأعمال</h2>
    <p class="section-lead" data-reveal>لمحة من التصاميم والهويات البصرية التي عملت عليها.</p>
    <div class="gallery-grid">
      <?php foreach ($gallery as $img): ?>
        <div class="gallery-item" data-reveal-scale data-reveal-group="gallery"
             data-lightbox="<?= e(UPLOAD_URL . '/' . $img['image_path']) ?>" data-caption="<?= e($img['caption']) ?>">
          <img src="<?= e(UPLOAD_URL . '/' . $img['image_path']) ?>" alt="<?= e($img['caption']) ?>" loading="lazy">
          <?php if ($img['caption']): ?><div class="gallery-caption"><?= e($img['caption']) ?></div><?php endif; ?>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<?php if ($caseStudies): ?>
<section class="section section-light" id="work" style="padding-top:0">
  <div class="container">
    <div class="kicker" data-reveal>أعمال منجزة</div>
    <h2 class="section-title" data-reveal>دراسات حالة</h2>
    <p class="section-lead" data-reveal>نماذج من نتائج تحققت لعملاء حقيقيين.</p>
    <?php foreach ($caseStudies as $cs): ?>
      <div class="case-study" data-reveal>
        <div class="case-image">
          <?php if ($cs['image_path']): ?>
            <img src="<?= e(UPLOAD_URL . '/' . $cs['image_path']) ?>" alt="<?= e($cs['title']) ?>" loading="lazy">
          <?php else: ?>
            <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.3" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="16" rx="1.5"/><circle cx="8.5" cy="9.5" r="1.5"/><path d="m21 16-5.5-5.5L3 20"/></svg>
          <?php endif; ?>
        </div>
        <div>
          <div class="case-title"><?= e($cs['title']) ?></div>
          <?php if ($cs['challenge']): ?><div class="case-row"><span class="case-tag case-tag--challenge">التحدي</span><span><?= e($cs['challenge']) ?></span></div><?php endif; ?>
          <?php if ($cs['solution']): ?><div class="case-row"><span class="case-tag">الحل</span><span><?= e($cs['solution']) ?></span></div><?php endif; ?>
          <?php if ($cs['result']): ?><div class="case-row"><span class="case-tag case-tag--result">النتيجة</span><span><?= e($cs['result']) ?></span></div><?php endif; ?>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
</section>
<?php endif; ?>

<?php if ($testimonials): ?>
<section class="section section-light" id="testimonials" style="padding-top:0">
  <div class="container">
    <div class="kicker" data-reveal>ثقة العملاء</div>
    <h2 class="section-title" data-reveal>آراء العملاء</h2>
    <p class="section-lead" data-reveal>ما يقوله أصحاب المشاريع الذين تعاونت معهم.</p>
    <div class="testimonial-slider" data-reveal>
      <div class="testimonial-track">
        <div class="testimonial-slides">
          <?php foreach ($testimonials as $t): ?>
            <div class="testimonial-slide">
              <div class="testimonial-card">
                <div class="testimonial-quote-icon">
                  <svg width="34" height="34" viewBox="0 0 24 24" fill="currentColor"><path d="M9.5 6C6.5 6 4 8.5 4 11.5S6.5 17 9.5 17c.3 0 .6 0 .9-.1-.6 1.8-2 3.3-3.9 4.1l.7 1c3-1.1 5.3-3.9 5.3-7.5V11c0-2.8-1.4-5-3-5zm9 0c-3 0-5.5 2.5-5.5 5.5S15.5 17 18.5 17c.3 0 .6 0 .9-.1-.6 1.8-2 3.3-3.9 4.1l.7 1c3-1.1 5.3-3.9 5.3-7.5V11c0-2.8-1.4-5-3-5z"/></svg>
                </div>
                <p class="testimonial-text">"<?= e($t['quote']) ?>"</p>
                <div class="testimonial-stars"><?= stars_svg((int) $t['rating']) ?></div>
                <div class="testimonial-author"><?= e($t['author']) ?></div>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
      <div class="testimonial-controls">
        <button class="testimonial-arrow testimonial-arrow--next" aria-label="التالي">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m9 6 6 6-6 6"/></svg>
        </button>
        <div class="testimonial-dots"></div>
        <button class="testimonial-arrow testimonial-arrow--prev" aria-label="السابق">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m15 6-6 6 6 6"/></svg>
        </button>
      </div>
    </div>
  </div>
</section>
<?php endif; ?>

<section class="section section-dark" id="contact">
  <div class="hero-orb hero-orb--gold" style="opacity:.25"></div>
  <div class="hero-orb hero-orb--emerald" style="opacity:.25"></div>
  <div class="container contact-grid" style="position:relative">
    <div data-reveal>
      <div class="kicker">خطوة نحو النمو</div>
      <h2 class="section-title"><?= e($settings['cta_heading']) ?></h2>
      <p class="section-lead" style="color:#c9c2b0"><?= e($settings['cta_text']) ?></p>
      <div class="contact-info">
        <div class="contact-info-item">
          <div class="contact-info-icon">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="m4 7 8 6 8-6"/></svg>
          </div>
          <div>
            <div class="contact-info-label">البريد الإلكتروني</div>
            <div class="contact-info-value" dir="ltr"><?= e($settings['email']) ?></div>
          </div>
        </div>
        <div class="contact-info-item">
          <div class="contact-info-icon">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><path d="M4 5c0 8.3 6.7 15 15 15l3-3.3c.3-.3.3-.9-.1-1.2l-4-3a1 1 0 0 0-1.2.1l-1.7 1.7a12 12 0 0 1-5.3-5.3l1.7-1.7c.3-.3.4-.9.1-1.2l-3-4A1 1 0 0 0 7.3 2L4 5z"/></svg>
          </div>
          <div>
            <div class="contact-info-label">رقم الهاتف</div>
            <div class="contact-info-value" dir="ltr"><?= e($settings['phone']) ?></div>
          </div>
        </div>
      </div>
    </div>
    <div data-reveal>
      <?php if ($flash): ?>
        <div class="form-alert <?= e($flash['type'] === 'success' ? 'success' : 'error') ?>"><?= e($flash['message']) ?></div>
      <?php endif; ?>
      <form class="contact-form" method="post" action="contact.php">
        <?= csrf_field() ?>
        <input type="text" name="website" class="contact-honeypot" tabindex="-1" autocomplete="off">
        <input type="text" name="name" placeholder="الاسم" required maxlength="150">
        <input type="email" name="email" placeholder="البريد الإلكتروني" required maxlength="150">
        <input type="text" name="phone" placeholder="رقم الهاتف (اختياري)" maxlength="50">
        <textarea name="message" placeholder="اكتبي تفاصيل مشروعك..." required maxlength="4000"></textarea>
        <button type="submit" class="btn btn-primary" style="justify-content:center">إرسال الرسالة</button>
      </form>
    </div>
  </div>
</section>

<footer class="site-footer">
  <div class="container">
    <div class="footer-name"><?= e($settings['site_name']) ?></div>
    <div class="footer-role"><?= e($settings['role_title']) ?></div>
    <div class="footer-rule"></div>
    <div class="footer-note">&copy; <?= (int) $year ?> <?= e($settings['footer_note']) ?></div>
  </div>
</footer>

<div class="lightbox">
  <button class="lightbox-close" aria-label="إغلاق">&times;</button>
  <img src="" alt="">
  <div class="lightbox-caption"></div>
</div>

<script src="assets/js/main.js"></script>
</body>
</html>
