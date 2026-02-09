<?php
// Start session
session_start();
define('FCPATH', dirname(__FILE__) . DIRECTORY_SEPARATOR);


// Load Composer autoloader (for packages like phpdotenv)
if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
    require_once __DIR__ . '/../vendor/autoload.php';

    // Load .env using vlucas/phpdotenv when available
    if (class_exists(\Dotenv\Dotenv::class)) {
        try {
            $dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__));
            $dotenv->safeLoad();
        } catch (Exception $e) {
            // ignore dotenv load errors; fallback to existing config
        }
        // Ensure variables loaded into $_ENV are also exported to the process
        // environment so `getenv()` returns them as well.
        if (!empty($_ENV)) {
            foreach ($_ENV as $k => $v) {
                if (!getenv($k) && is_string($v)) {
                    putenv($k . '=' . $v);
                    $_SERVER[$k] = $v;
                }
            }
        }
    }
}

// Load configuration
require_once '../config/config.php';
if (!defined('PUBLIC_PATH')) {
    define('PUBLIC_PATH', realpath(__DIR__) . DIRECTORY_SEPARATOR);
}

// Autoload classes
spl_autoload_register(function ($className) {
    $paths = [
        '../app/controllers/',
        '../app/models/',
        '../app/core/',
        '../app/services/Emcf/'
    ];
    
    foreach ($paths as $path) {
        $file = $path . $className . '.php';
        if (file_exists($file)) {
            require_once $file;
            return;
        }
    }
});

// Initialize core application
$app = new App();

// case 'banklist':
//     $controller = new BankListController();
//     $controller->banklist();
//     break;

// case 'banklist/crudData':
//     $controller = new BankListController();
//     $controller->crudData($_GET['action'] ?? 'insertion');
//     break;

// case 'banklist/getBankById':
//     $controller = new BankListController();
//     $controller->getBankById();
//     break;
