<?php
declare(strict_types=1);

use App\Config\App;
use App\Core\Auth;
use App\Core\Csrf;
use App\Core\Session;

/** Escape output for HTML context. */
function e(mixed $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/** Absolute application URL. */
function url(string $path = ''): string
{
    return App::url($path);
}

/**
 * URL to a file inside public/assets.
 *
 * The file modification time is appended so browsers refresh cached CSS/JS
 * after a deployment instead of serving a stale copy.
 */
function asset(string $path): string
{
    $path = ltrim($path, '/');
    $url  = App::url('assets/' . $path);
    $file = APP_ROOT . '/public/assets/' . $path;

    if (is_file($file)) {
        $url .= '?v=' . filemtime($file);
    }

    return $url;
}

/** Hidden CSRF input field. */
function csrf_field(): string
{
    return Csrf::field();
}

/** Read and consume a flash message. */
function flash(string $key): ?string
{
    return Session::pullFlash($key);
}

/** Previously submitted form value. */
function old(string $key, string $default = ''): string
{
    return e(Session::old($key, $default));
}

/** Current authenticated user array or null. */
function auth_user(): ?array
{
    return Auth::user();
}

/** Is the current user an administrator? */
function is_admin(): bool
{
    return Auth::isAdmin();
}

/** Sidebar active-state helper. */
function nav_active(string $prefix): string
{
    return App::is($prefix) ? 'active' : '';
}

/** Human readable file size. */
function human_size(int|float|null $bytes): string
{
    $bytes = (float) ($bytes ?? 0);
    if ($bytes < 1024) {
        return number_format($bytes) . ' B';
    }
    $units = ['KB', 'MB', 'GB', 'TB'];
    $i = -1;
    do {
        $bytes /= 1024;
        $i++;
    } while ($bytes >= 1024 && $i < count($units) - 1);

    return number_format($bytes, 1) . ' ' . $units[$i];
}

/** Format a MySQL datetime. */
function fmt_date(?string $value, string $format = 'd M Y'): string
{
    if (!$value) {
        return '-';
    }
    $ts = strtotime($value);
    return $ts ? date($format, $ts) : '-';
}

/** Format a MySQL datetime including time. */
function fmt_datetime(?string $value): string
{
    return fmt_date($value, 'd M Y H:i');
}

/** Initials from a full name, for avatars. */
function initials(?string $name): string
{
    $parts = preg_split('/\s+/', trim((string) $name)) ?: [];
    $out = '';
    foreach (array_slice($parts, 0, 2) as $part) {
        if ($part !== '') {
            $out .= mb_strtoupper(mb_substr($part, 0, 1));
        }
    }
    return $out !== '' ? $out : '?';
}

/**
 * URL of a user's profile photo, or null when the user has no photo.
 * A short version query keeps browsers from showing a cached old photo.
 */
function avatar_url(?array $user): ?string
{
    if (!$user || empty($user['photo_id']) || empty($user['id'])) {
        return null;
    }

    return url('/avatar/' . (int) $user['id']) . '?v=' . substr(md5((string) $user['photo_id']), 0, 8);
}

/** Avatar markup: photo when available, otherwise initials. */
function avatar_html(?array $user, string $extraClass = ''): string
{
    $photo = avatar_url($user);
    $class = trim('avatar ' . $extraClass);

    if ($photo !== null) {
        return '<img src="' . e($photo) . '" alt="Foto profil" class="' . e($class) . ' avatar-img">';
    }

    return '<span class="' . e($class) . ' avatar-initials">' . e(initials($user['fullname'] ?? '')) . '</span>';
}

/** URL of an announcement's featured image, or null when it has none. */
function news_image_url(?array $announcement): ?string
{
    if (!$announcement || empty($announcement['image_id']) || empty($announcement['id'])) {
        return null;
    }

    return url('/announcements/' . (int) $announcement['id'] . '/image')
        . '?v=' . substr(md5((string) $announcement['image_id']), 0, 8);
}

/** Short excerpt for news cards. */
function excerpt(?string $text, int $length = 140): string
{
    $text = trim(preg_replace('/\s+/', ' ', (string) $text) ?? '');
    if ($text === '') {
        return '';
    }

    return mb_strimwidth($text, 0, $length, '...');
}

/** Estimated reading time in minutes. */
function reading_time(?string $text): int
{
    $words = str_word_count(strip_tags((string) $text));

    return max(1, (int) ceil($words / 200));
}
