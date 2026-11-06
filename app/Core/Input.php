<?php
declare(strict_types=1);

namespace App\Core;

/**
 * Request input accessor with light normalisation.
 */
final class Input
{
    public static function get(string $key, mixed $default = null): mixed
    {
        return $_GET[$key] ?? $default;
    }

    public static function post(string $key, mixed $default = null): mixed
    {
        return $_POST[$key] ?? $default;
    }

    /** Trimmed string from POST, falling back to GET. */
    public static function str(string $key, string $default = ''): string
    {
        $value = $_POST[$key] ?? $_GET[$key] ?? $default;
        return is_scalar($value) ? trim((string) $value) : $default;
    }

    public static function int(string $key, int $default = 0): int
    {
        $value = $_POST[$key] ?? $_GET[$key] ?? null;
        return is_numeric($value) ? (int) $value : $default;
    }

    public static function bool(string $key): bool
    {
        $value = $_POST[$key] ?? $_GET[$key] ?? null;
        return in_array($value, ['1', 'on', 'true', 'yes', 1, true], true);
    }

    public static function file(string $key): ?array
    {
        return isset($_FILES[$key]) && is_array($_FILES[$key]) ? $_FILES[$key] : null;
    }

    public static function all(): array
    {
        return array_merge($_GET, $_POST);
    }
}
