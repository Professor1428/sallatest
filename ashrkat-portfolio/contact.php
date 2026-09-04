<?php
require_once __DIR__ . '/includes/bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('index.php');
}

verify_csrf();

// Honeypot: a real visitor never fills this hidden field; a bot usually does.
if (!empty($_POST['website'])) {
    redirect('index.php#contact');
}

$name = trim($_POST['name'] ?? '');
$email = trim($_POST['email'] ?? '');
$phone = trim($_POST['phone'] ?? '');
$body = trim($_POST['message'] ?? '');

if ($name === '' || $body === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    flash_set('error', 'يرجى تعبئة الاسم والبريد الإلكتروني الصحيح ونص الرسالة.');
    redirect('index.php#contact');
}

if (mb_strlen($name) > 150 || mb_strlen($body) > 4000) {
    flash_set('error', 'الرسالة طويلة جدًا.');
    redirect('index.php#contact');
}

$pdo = db();
$pdo->prepare('INSERT INTO messages (name, email, phone, body) VALUES (?, ?, ?, ?)')
    ->execute([$name, $email, $phone, $body]);

flash_set('success', 'تم إرسال رسالتك بنجاح، وسيتم التواصل معك في أقرب وقت.');
redirect('index.php#contact');
