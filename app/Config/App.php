<?php
declare(strict_types=1);

namespace App\Config;

/**
 * Application identity and URL helpers.
 */
final class App
{
    public const NAME    = 'Company Portal';
    public const VERSION = '3.0.0';

    private static string $base = '';
    private static string $path = '/';

    public static function boot(): void
    {
        $script = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '/index.php');
        $dir    = rtrim(dirname($script), '/');

        if (str_ends_with($dir, '/public')) {
            $dir = substr($dir, 0, -7);
        }
        if ($dir === '/' || $dir === '.' || $dir === '\\') {
            $dir = '';
        }
        self::$base = $dir;

        $uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
        if (self::$base !== '' && str_starts_with($uri, self::$base)) {
            $uri = substr($uri, strlen(self::$base));
        }
        $uri = '/' . trim($uri, '/');
        $uri = preg_replace('#/(public/)?index\.php$#', '', $uri) ?: '/';
        self::$path = $uri === '' ? '/' : $uri;
    }

    public static function base(): string
    {
        return self::$base;
    }

    public static function path(): string
    {
        return self::$path;
    }

    public static function url(string $path = ''): string
    {
        if (preg_match('#^https?://#i', $path) === 1) {
            return $path;
        }
        if ($path === '' || $path === '/') {
            return self::$base . '/';
        }
        return self::$base . '/' . ltrim($path, '/');
    }

    /** True when the current request path starts with the given prefix. */
    public static function is(string $prefix): bool
    {
        $prefix = '/' . trim($prefix, '/');
        return self::$path === $prefix || str_starts_with(self::$path, $prefix . '/');
    }
}
