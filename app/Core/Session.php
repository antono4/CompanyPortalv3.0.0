<?php
declare(strict_types=1);

namespace App\Core;

/**
 * Session wrapper with flash + old-input support.
 */
final class Session
{
    public static function start(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            return;
        }

        $dir = APP_ROOT . '/storage/sessions';
        if (!is_dir($dir)) {
            @mkdir($dir, 0775, true);
        }
        if (is_dir($dir) && is_writable($dir)) {
            session_save_path($dir);
        }

        session_set_cookie_params([
            'lifetime' => 0,
            'path'     => \App\Config\App::base() !== '' ? \App\Config\App::base() : '/',
            'httponly' => true,
            'samesite' => 'Lax',
        ]);
        session_name('INTRANETPORTALSESSID');
        session_start();
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        return $_SESSION[$key] ?? $default;
    }

    public static function set(string $key, mixed $value): void
    {
        $_SESSION[$key] = $value;
    }

    public static function has(string $key): bool
    {
        return isset($_SESSION[$key]);
    }

    public static function forget(string $key): void
    {
        unset($_SESSION[$key]);
    }

    public static function regenerate(): void
    {
        session_regenerate_id(true);
    }

    /** Store a flash message for the next request. */
    public static function flash(string $key, string $message): void
    {
        $_SESSION['_flash'][$key] = $message;
    }

    /** Read and remove a flash message. */
    public static function pullFlash(string $key): ?string
    {
        $message = $_SESSION['_flash'][$key] ?? null;
        unset($_SESSION['_flash'][$key]);
        return $message;
    }

    /** Remember submitted input for the next request. */
    public static function setOld(array $data): void
    {
        unset($data['csrf_token'], $data['password'], $data['password_confirmation'],
            $data['current_password'], $data['new_password']);
        $_SESSION['_old'] = $data;
    }

    public static function old(string $key, string $default = ''): string
    {
        $value = $_SESSION['_old'][$key] ?? $default;
        return is_scalar($value) ? (string) $value : $default;
    }

    public static function clearOld(): void
    {
        unset($_SESSION['_old']);
    }

    public static function destroy(): void
    {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'] ?? '', (bool) $params['secure'], (bool) $params['httponly']);
        }
        session_destroy();
    }
}
