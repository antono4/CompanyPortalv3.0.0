<?php
declare(strict_types=1);

namespace App\Core;

/**
 * Cross-site request forgery protection.
 */
final class Csrf
{
    public static function token(): string
    {
        if (empty($_SESSION['_csrf_token'])) {
            $_SESSION['_csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['_csrf_token'];
    }

    public static function field(): string
    {
        return '<input type="hidden" name="csrf_token" value="' . self::token() . '">';
    }

    public static function verify(mixed $token): bool
    {
        return is_string($token)
            && $token !== ''
            && hash_equals($_SESSION['_csrf_token'] ?? '', $token);
    }
}
