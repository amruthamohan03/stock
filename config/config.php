<?php
/**
 * Main Application Configuration
 */

define('APP_ROOT',   dirname(dirname(__FILE__)));
define('PUBLIC_PATH', APP_ROOT . '/public');
define('BASE_URL',   'http://localhost/stock/public');
define('VIEW_PATH',  __DIR__ . '/../app/views/');
define('APP_URL',    'http://localhost/stock/');
define('UPLOAD_URL', BASE_URL . '/uploads/');

$dbConfig = require_once APP_ROOT . '/config/database.php';
define('DB_CONFIG', $dbConfig);

define('APP_NAME',    'Stock');
define('APP_VERSION', '1.0.0');
define('APP_ENV',     'development');

define('URL_ROOT',      '/stock');
define('URL_SUBFOLDER', '');

define('DB_CONNECTION', $dbConfig['default']);
define('DB_HOST',    $dbConfig['connections'][$dbConfig['default']]['host']);
define('DB_PORT',    $dbConfig['connections'][$dbConfig['default']]['port']     ?? 3306);
define('DB_NAME',    $dbConfig['connections'][$dbConfig['default']]['database']);
define('DB_USER',    $dbConfig['connections'][$dbConfig['default']]['username']);
define('DB_PASS',    $dbConfig['connections'][$dbConfig['default']]['password']);
define('DB_CHARSET', $dbConfig['connections'][$dbConfig['default']]['charset']  ?? 'utf8mb4');

// ── Session timeouts (ALL IN SECONDS) ────────────────────────────────────────
//  SESSION_TIMEOUT      = idle time before logout  ← must match Sessionmanager.php
//  SESSION_WARNING_TIME = warn user this many seconds before SESSION_TIMEOUT
//  SESSION_LIFETIME     = PHP cookie/GC max lifetime (>= SESSION_TIMEOUT)
// ─────────────────────────────────────────────────────────────────────────────
define('SESSION_TIMEOUT',      3600);   // 1 hour
define('SESSION_WARNING_TIME',  300);   // 5 minutes warning
define('SESSION_LIFETIME',     7200);   // 2 hours absolute max

define('SESSION_NAME',     'mvc_session');
define('SESSION_PATH',     '/');
define('SESSION_DOMAIN',   '');
define('SESSION_SECURE',   false);
define('SESSION_HTTPONLY', true);

define('HASH_ALGO',        PASSWORD_DEFAULT);
define('HASH_COST',        12);
define('CSRF_TOKEN_NAME',  'csrf_token');
define('CSRF_TOKEN_LENGTH', 32);

define('UPLOAD_PATH',        PUBLIC_PATH . '/uploads');
define('MAX_FILE_SIZE',      5242880);
define('ALLOWED_FILE_TYPES', ['jpg', 'jpeg', 'png', 'gif', 'pdf', 'doc', 'docx']);

define('MAIL_HOST',         'smtp.gmail.com');
define('MAIL_PORT',         587);
define('MAIL_USERNAME',     'your-email@gmail.com');
define('MAIL_PASSWORD',     'your-app-password');
define('MAIL_FROM_ADDRESS', 'noreply@example.com');
define('MAIL_FROM_NAME',    APP_NAME);

define('ITEMS_PER_PAGE',  10);
define('CACHE_ENABLED',   false);
define('CACHE_LIFETIME',  3600);

define('LOG_PATH',  APP_ROOT . '/logs');
define('LOG_FILE',  LOG_PATH . '/app.log');
define('LOG_LEVEL', 'debug');

switch (APP_ENV) {
    case 'production':
        error_reporting(0);
        ini_set('display_errors', 0);
        ini_set('log_errors', 1);
        ini_set('error_log', LOG_PATH . '/error.log');
        define('DEBUG_MODE', false);
        break;
    case 'staging':
        error_reporting(E_ALL & ~E_NOTICE);
        ini_set('display_errors', 1);
        ini_set('log_errors', 1);
        ini_set('error_log', LOG_PATH . '/staging-error.log');
        define('DEBUG_MODE', true);
        break;
    case 'development':
    default:
        error_reporting(E_ALL);
        ini_set('display_errors', 1);
        ini_set('log_errors', 1);
        ini_set('error_log', LOG_PATH . '/dev-error.log');
        define('DEBUG_MODE', true);
        break;
}

date_default_timezone_set('Africa/Lubumbashi');
define('DATE_FORMAT',     'd-m-Y');
define('DATETIME_FORMAT', 'd-m-Y H:i:s');

mb_internal_encoding('UTF-8');
mb_http_output('UTF-8');

if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.gc_maxlifetime',  SESSION_LIFETIME);
    ini_set('session.cookie_lifetime', 0);
    ini_set('session.cookie_path',     SESSION_PATH);
    ini_set('session.cookie_domain',   SESSION_DOMAIN);
    ini_set('session.cookie_secure',   SESSION_SECURE);
    ini_set('session.cookie_httponly', SESSION_HTTPONLY);
    ini_set('session.use_strict_mode', 1);
    ini_set('session.use_only_cookies', 1);
    ini_set('session.name', SESSION_NAME);
}

foreach ([LOG_PATH, UPLOAD_PATH, APP_ROOT . '/cache'] as $dir) {
    if (!is_dir($dir)) mkdir($dir, 0755, true);
}