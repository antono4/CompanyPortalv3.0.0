<?php
declare(strict_types=1);

namespace App\Core;

/**
 * Session based authentication.
 */
final class Auth
{
    private static ?array $user = null;
    private static bool $loaded = false;

    public static function attempt(string $login, string $password): ?array
    {
        $model = new \App\Models\User();
        $user  = $model->findByLogin($login);

        if (!$user) {
            return null;
        }
        if (strtolower((string) $user['status']) !== 'active') {
            return null;
        }
        if (!password_verify($password, (string) $user['password'])) {
            return null;
        }

        return $user;
    }

    public static function login(array $user): void
    {
        Session::regenerate();
        Session::set('user', [
            'id'            => (int) $user['id'],
            'fullname'      => $user['fullname'],
            'username'      => $user['username'],
            'email'         => $user['email'],
            'photo_id'      => $user['photo_id'] !== null ? (int) $user['photo_id'] : null,
            'role'          => strtolower((string) $user['role']),
            'department_id' => $user['department_id'] !== null ? (int) $user['department_id'] : null,
        ]);
        self::$user   = Session::get('user');
        self::$loaded = true;
    }

    public static function user(): ?array
    {
        if (!self::$loaded) {
            $user = Session::get('user');
            self::$user = is_array($user) ? $user : null;
            self::$loaded = true;
        }
        return self::$user;
    }

    public static function check(): bool
    {
        return self::user() !== null;
    }

    public static function id(): ?int
    {
        $user = self::user();
        return $user !== null ? (int) $user['id'] : null;
    }

    public static function isAdmin(): bool
    {
        $user = self::user();
        return $user !== null && ($user['role'] ?? '') === 'admin';
    }

    public static function logout(): void
    {
        self::$user   = null;
        self::$loaded = false;
        Session::destroy();
    }
}
