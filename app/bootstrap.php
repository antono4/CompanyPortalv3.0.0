<?php
declare(strict_types=1);

/**
 * Company Portal - application bootstrap.
 */

define('APP_ROOT', dirname(__DIR__));
define('APP_VIEW_PATH', APP_ROOT . '/app/Views');

date_default_timezone_set('Asia/Jakarta');

error_reporting(E_ALL);
ini_set('display_errors', '1');
ini_set('log_errors', '1');

$logDir = APP_ROOT . '/storage/logs';
if (!is_dir($logDir)) {
    @mkdir($logDir, 0775, true);
}
if (is_dir($logDir)) {
    ini_set('error_log', $logDir . '/php-error.log');
}

spl_autoload_register(static function (string $class): void {
    $prefix = 'App\\';
    if (!str_starts_with($class, $prefix)) {
        return;
    }
    $relative = substr($class, strlen($prefix));
    $file     = APP_ROOT . '/app/' . str_replace('\\', '/', $relative) . '.php';
    if (is_file($file)) {
        require $file;
    }
});

require APP_ROOT . '/app/Core/helpers.php';

\App\Config\App::boot();
\App\Core\Session::start();
