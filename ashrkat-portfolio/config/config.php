<?php
/**
 * Main configuration file.
 * On shared hosting with MySQL: set DB_DRIVER to 'mysql' and fill the credentials below.
 * With no database available: leave DB_DRIVER as 'sqlite' — a local file database is created
 * automatically the first time the site runs, no setup required.
 */

declare(strict_types=1);

// ---- Database ----------------------------------------------------------
define('DB_DRIVER', 'sqlite'); // 'sqlite' or 'mysql'

// Used only when DB_DRIVER = 'mysql'
define('DB_HOST', '127.0.0.1');
define('DB_NAME', 'ashrkat_portfolio');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

// Used only when DB_DRIVER = 'sqlite'
define('SQLITE_PATH', __DIR__ . '/../data/database.sqlite');

// ---- Security ------------------------------------------------------------
// One-time secret required to create the FIRST admin account via admin/install.php.
// Change this to your own random string before deploying, then forget the default.
define('SETUP_KEY', 'ashrkat-setup-9f3d7c1b');

// Set to true once the site is served over HTTPS (recommended in production).
define('FORCE_HTTPS', false);

// Maximum failed login attempts allowed per IP within LOGIN_LOCKOUT_WINDOW seconds.
define('LOGIN_MAX_ATTEMPTS', 5);
define('LOGIN_LOCKOUT_WINDOW', 900); // 15 minutes

// ---- Uploads ---------------------------------------------------------
define('UPLOAD_DIR', __DIR__ . '/../uploads');
define('UPLOAD_URL', 'uploads');
define('MAX_UPLOAD_BYTES', 5 * 1024 * 1024); // 5 MB
define('ALLOWED_IMAGE_TYPES', ['image/jpeg', 'image/png', 'image/webp']);

// ---- Site meta -------------------------------------------------------
define('SITE_TIMEZONE', 'Asia/Riyadh');
