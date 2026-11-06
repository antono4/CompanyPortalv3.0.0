<?php
declare(strict_types=1);

namespace App\Core;

use RuntimeException;

/**
 * Base controller with view rendering, redirects and guards.
 */
abstract class Controller
{
    protected function view(string $view, array $data = []): void
    {
        $file = APP_VIEW_PATH . '/' . ltrim($view, '/') . '.php';
        if (!is_file($file)) {
            throw new RuntimeException('View tidak ditemukan: ' . $view);
        }
        extract($data, EXTR_SKIP);
        require $file;
    }

    protected function redirect(string $path): never
    {
        header('Location: ' . \App\Config\App::url($path));
        exit;
    }

    protected function back(): never
    {
        $referer = $_SERVER['HTTP_REFERER'] ?? '';
        $host    = $_SERVER['HTTP_HOST'] ?? '';
        if ($referer !== '' && ($host === '' || str_contains($referer, $host))) {
            header('Location: ' . $referer);
            exit;
        }
        $this->redirect('/dashboard');
    }

    protected function requireAuth(): void
    {
        if (!Auth::check()) {
            Session::set('redirect_after_login', \App\Config\App::path());
            Session::flash('error', 'Silakan masuk terlebih dahulu.');
            $this->redirect('/login');
        }
    }

    protected function requireAdmin(): void
    {
        $this->requireAuth();
        if (!Auth::isAdmin()) {
            http_response_code(403);
            $this->view('errors/403', ['title' => 'Akses Ditolak']);
            exit;
        }
    }

    protected function sendFile(
        string $path,
        string $name,
        string $type = 'application/octet-stream',
        bool $inline = false
    ): never {
        while (ob_get_level() > 0) {
            ob_end_clean();
        }
        $fallback    = preg_replace('/[^A-Za-z0-9._ -]/', '_', $name) ?: 'download';
        $disposition = $inline ? 'inline' : 'attachment';

        header('Content-Description: File Transfer');
        header('Content-Type: ' . ($type !== '' ? $type : 'application/octet-stream'));
        header('Content-Disposition: ' . $disposition . '; filename="' . $fallback . '"; filename*=UTF-8\'\'' . rawurlencode($name));
        header('Content-Length: ' . (string) filesize($path));
        header('X-Content-Type-Options: nosniff');
        header('Cache-Control: private, max-age=0, must-revalidate');
        readfile($path);
        exit;
    }

    protected function sendBlob(
        string $data,
        string $name,
        string $type = 'application/octet-stream',
        bool $inline = false
    ): never {
        while (ob_get_level() > 0) {
            ob_end_clean();
        }
        $fallback    = preg_replace('/[^A-Za-z0-9._ -]/', '_', $name) ?: 'file';
        $disposition = $inline ? 'inline' : 'attachment';

        header('Content-Description: File Transfer');
        header('Content-Type: ' . ($type !== '' ? $type : 'application/octet-stream'));
        header('Content-Disposition: ' . $disposition . '; filename="' . $fallback . '"; filename*=UTF-8\'\'' . rawurlencode($name));
        header('Content-Length: ' . (string) strlen($data));
        header('X-Content-Type-Options: nosniff');
        header('Cache-Control: private, max-age=0, must-revalidate');
        echo $data;
        exit;
    }

    protected function abort(int $code = 404): never
    {
        http_response_code($code);
        $view = is_file(APP_VIEW_PATH . "/errors/{$code}.php") ? "errors/{$code}" : 'errors/404';
        $messages = [
            403 => 'Akses Ditolak',
            404 => 'Halaman Tidak Ditemukan',
            500 => 'Terjadi Kesalahan',
        ];
        $this->view($view, ['title' => $messages[$code] ?? 'Error']);
        exit;
    }

    /** Send a JSON response and stop. */
    protected function json(array $data, int $status = 200): never
    {
        while (ob_get_level() > 0) {
            ob_end_clean();
        }
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        header('Cache-Control: no-store, no-cache, must-revalidate');
        header('X-Content-Type-Options: nosniff');
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }

    protected function verifyCsrf(): void
    {
        if (!Csrf::verify(Input::post('csrf_token'))) {
            Session::flash('error', 'Sesi formulir kedaluwarsa. Silakan coba lagi.');
            $this->back();
        }
    }

    protected function validate(array $rules): array
    {
        return Validator::validate($rules);
    }

    protected function input(): array
    {
        return [
            'post' => $_POST,
            'get'  => $_GET,
        ];
    }
}
