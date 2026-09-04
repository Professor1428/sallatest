<?php
declare(strict_types=1);

function e(?string $value): string
{
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

function nl_list(?string $text): array
{
    if (!$text) {
        return [];
    }
    $lines = preg_split('/\r\n|\r|\n/', $text);
    return array_values(array_filter(array_map('trim', $lines), fn($l) => $l !== ''));
}

function redirect(string $path): never
{
    header('Location: ' . $path);
    exit;
}

function flash_set(string $type, string $message): void
{
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function flash_get(): ?array
{
    if (empty($_SESSION['flash'])) {
        return null;
    }
    $flash = $_SESSION['flash'];
    unset($_SESSION['flash']);
    return $flash;
}

function client_ip(): string
{
    return $_SERVER['REMOTE_ADDR'] ?? 'unknown';
}

function too_many_attempts(PDO $pdo, string $ip): bool
{
    $stmt = $pdo->prepare('SELECT COUNT(*) AS c FROM login_attempts WHERE ip = ? AND created_at > ?');
    $cutoff = gmdate('Y-m-d H:i:s', time() - LOGIN_LOCKOUT_WINDOW);
    $stmt->execute([$ip, $cutoff]);
    return (int) $stmt->fetch()['c'] >= LOGIN_MAX_ATTEMPTS;
}

function record_attempt(PDO $pdo, string $ip): void
{
    $pdo->prepare('INSERT INTO login_attempts (ip) VALUES (?)')->execute([$ip]);
}

function clear_attempts(PDO $pdo, string $ip): void
{
    $pdo->prepare('DELETE FROM login_attempts WHERE ip = ?')->execute([$ip]);
}

/**
 * Validate and store an uploaded image safely: checks real MIME type, re-encodes the
 * pixel data through GD (stripping any non-image payload hidden in the file), and saves
 * it under a random name so uploaded files can never be executed as scripts.
 */
function handle_image_upload(array $file, string $subdir): ?string
{
    if (!isset($file['error']) || $file['error'] !== UPLOAD_ERR_OK) {
        return null;
    }
    if ($file['size'] > MAX_UPLOAD_BYTES) {
        throw new RuntimeException('حجم الصورة أكبر من الحد المسموح (5 ميجابايت).');
    }

    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->file($file['tmp_name']);
    if (!in_array($mime, ALLOWED_IMAGE_TYPES, true)) {
        throw new RuntimeException('صيغة الصورة غير مدعومة. الصيغ المسموحة: JPG، PNG، WEBP.');
    }

    $image = match ($mime) {
        'image/jpeg' => imagecreatefromjpeg($file['tmp_name']),
        'image/png' => imagecreatefrompng($file['tmp_name']),
        'image/webp' => imagecreatefromwebp($file['tmp_name']),
        default => null,
    };
    if ($image === false || $image === null) {
        throw new RuntimeException('تعذّر قراءة ملف الصورة.');
    }

    $ext = match ($mime) {
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
    };
    $filename = bin2hex(random_bytes(16)) . '.' . $ext;
    $destDir = rtrim(UPLOAD_DIR, '/') . '/' . $subdir;
    if (!is_dir($destDir)) {
        mkdir($destDir, 0775, true);
    }
    $destPath = $destDir . '/' . $filename;

    $saved = match ($ext) {
        'jpg' => imagejpeg($image, $destPath, 88),
        'png' => imagepng($image, $destPath, 6),
        'webp' => imagewebp($image, $destPath, 88),
    };
    imagedestroy($image);

    if (!$saved) {
        throw new RuntimeException('تعذّر حفظ الصورة على الخادم.');
    }

    return $subdir . '/' . $filename;
}

function delete_uploaded_image(?string $relativePath): void
{
    if (!$relativePath) {
        return;
    }
    $full = rtrim(UPLOAD_DIR, '/') . '/' . ltrim($relativePath, '/');
    if (is_file($full)) {
        @unlink($full);
    }
}

const ICON_LIBRARY = [
    'megaphone' => '<path d="M3 11v2a2 2 0 0 0 2 2h1l3 5V4L6 9H5a2 2 0 0 0-2 2z"/><path d="M14 8a4 4 0 0 1 0 8"/><path d="M17 5a8 8 0 0 1 0 14"/>',
    'brush' => '<path d="M9.06 11.9l8.07-8.06a2.85 2.85 0 1 1 4.03 4.03l-8.06 8.08"/><path d="M7.07 14.94c-1.66 0-3 1.35-3 3.02 0 1.33-2.5 1.52-2 2.02 1.08 1.1 2.49 2.02 4 2.02 2.2 0 4-1.8 4-4.04a3.01 3.01 0 0 0-3-3.02z"/>',
    'tools' => '<path d="M14.7 6.3a4 4 0 0 0-5.6 4.9L2 18.3l1.4 1.4 7.1-7.1a4 4 0 0 0 4.9-5.6l-2.6 2.6-2-2 2.6-2.6z"/>',
    'camera' => '<path d="M4 8h3l1.5-2h7L17 8h3a1 1 0 0 1 1 1v9a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V9a1 1 0 0 1 1-1z"/><circle cx="12" cy="13" r="3.3"/>',
    'chart' => '<path d="M4 19V5"/><path d="M4 19h16"/><rect x="7" y="11" width="2.6" height="6"/><rect x="12" y="8" width="2.6" height="9"/><rect x="17" y="13" width="2.6" height="4"/>',
    'video' => '<rect x="3" y="6" width="12" height="12" rx="1.5"/><path d="m15 10 6-3v10l-6-3z"/>',
];

function icon_svg(string $key, int $size = 28, string $color = 'currentColor'): string
{
    $path = ICON_LIBRARY[$key] ?? ICON_LIBRARY['megaphone'];
    return sprintf(
        '<svg width="%1$d" height="%1$d" viewBox="0 0 24 24" fill="none" stroke="%2$s" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">%3$s</svg>',
        $size,
        e($color),
        $path
    );
}

/**
 * Swap sort_order with the neighbouring row. $table is always a hardcoded string from the
 * call site (never user input), so it is safe to interpolate into the SQL identifier position.
 */
function reorder_move(PDO $pdo, string $table, int $id, string $direction): void
{
    $comparator = $direction === 'up' ? '<' : '>';
    $order = $direction === 'up' ? 'DESC' : 'ASC';

    $stmt = $pdo->prepare("SELECT id, sort_order FROM $table WHERE id = ?");
    $stmt->execute([$id]);
    $current = $stmt->fetch();
    if (!$current) {
        return;
    }

    $stmt = $pdo->prepare("SELECT id, sort_order FROM $table WHERE sort_order $comparator ? ORDER BY sort_order $order LIMIT 1");
    $stmt->execute([$current['sort_order']]);
    $neighbor = $stmt->fetch();
    if (!$neighbor) {
        return;
    }

    $pdo->prepare("UPDATE $table SET sort_order = ? WHERE id = ?")->execute([$neighbor['sort_order'], $current['id']]);
    $pdo->prepare("UPDATE $table SET sort_order = ? WHERE id = ?")->execute([$current['sort_order'], $neighbor['id']]);
}

function stars_svg(int $rating): string
{
    $rating = max(0, min(5, $rating));
    $out = '';
    for ($i = 0; $i < 5; $i++) {
        $opacity = $i < $rating ? '1' : '0.25';
        $out .= '<svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor" style="opacity:' . $opacity . '"><path d="M12 2l2.9 6.26L21.8 9l-5 4.87L18.2 21 12 17.27 5.8 21l1.4-7.13-5-4.87 6.9-.74L12 2z"/></svg>';
    }
    return $out;
}
